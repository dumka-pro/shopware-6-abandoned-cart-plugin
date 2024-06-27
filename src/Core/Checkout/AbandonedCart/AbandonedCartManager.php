<?php

declare(strict_types=1);

namespace MailCampaigns\AbandonedCart\Core\Checkout\AbandonedCart;

use Doctrine\DBAL\Exception;
use MailCampaigns\AbandonedCart\Core\Checkout\Cart\CartRepository;
use MailCampaigns\AbandonedCart\Core\Event\DeleteAbandonedCartEvent;
use MailCampaigns\AbandonedCart\Core\Event\MarkAbandonedCartEvent;
use Shopware\Core\Checkout\Cart\AbstractCartPersister;
use Shopware\Core\Framework\Api\Context\SystemSource;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

/**
 * @author Twan Haverkamp <twan@mailcampaigns.nl>
 */
final class AbandonedCartManager
{
    public function __construct(
        private readonly CartRepository $cartRepository,
        private readonly EntityRepository $abandonedCartRepository,
        private EventDispatcherInterface $eventDispatcher
    ) {
    }

    /**
     * @return int The number of generated "abandoned" carts.
     * @throws Exception
     */
    public function generate(): int
    {
        $cnt = 0;

        foreach ($this->cartRepository->findMarkableAsAbandoned() as $cart) {
            $abandonedCart = AbandonedCartFactory::createFromArray($cart);

            $context = new Context(new SystemSource());
            /** @var  \Shopware\Core\Framework\DataAbstractionLayer\Event\EntityWrittenContainerEvent $res */
            $res = $this->abandonedCartRepository->upsert([
                [
                    'cartToken' => $abandonedCart->getCartToken(),
                    'price' => $abandonedCart->getPrice(),
                    'lineItems' => $abandonedCart->getLineItems(),
                    'customerId' => $abandonedCart->getCustomerId(),
                    'salesChannelId' => $abandonedCart->getSalesChannelId(),
                ],
            ],$context);
            $this->eventDispatcher->dispatch(new MarkAbandonedCartEvent($context,$cart),MarkAbandonedCartEvent::EVENT_NAME);
            $cnt++;
        }

        return $cnt;
    }

    /**
     * @return int The number of deleted "abandoned" carts.
     * @throws Exception
     */
    public function cleanUp(): int
    {
        $cnt = 0;

        foreach ($this->cartRepository->findTokensForUpdatedOrDeletedWithAbandonedCartAssociation() as $token) {
            $abandonedCartId = $this->findAbandonedCartIdByToken($token);
            $context = new Context(new SystemSource());

            if ($abandonedCartId !== null) {
                $abandonedCartEntity = $this->abandonedCartRepository->search(new Criteria([$abandonedCartId]),$context)->get($abandonedCartId);
                $this->eventDispatcher->dispatch(new DeleteAbandonedCartEvent($context,$abandonedCartEntity),DeleteAbandonedCartEvent::EVENT_NAME);
                $this->abandonedCartRepository->delete([
                    [
                        'id' => $abandonedCartId,
                    ],
                ], $context);

                $cnt++;
            }
        }

        return $cnt;
    }

    private function findAbandonedCartIdByToken(string $token): ?string
    {
        $criteria = new Criteria();
        $criteria->addFilter(new EqualsFilter('cartToken', $token));

        return $this->abandonedCartRepository
            ->searchIds($criteria, new Context(new SystemSource()))
            ->firstId();
    }
}
