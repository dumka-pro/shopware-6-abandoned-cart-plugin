<?php
namespace MailCampaigns\AbandonedCart\Core\Event;

use MailCampaigns\AbandonedCart\Core\Checkout\AbandonedCart\AbandonedCartDefinition;
use MailCampaigns\AbandonedCart\Core\Checkout\AbandonedCart\AbandonedCartEntity;
use Shopware\Core\Content\Flow\Dispatching\Aware\ScalarValuesAware;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\Event\EventData\EntityType;
use Shopware\Core\Framework\Event\EventData\EventDataCollection;
use Shopware\Core\Framework\Event\EventData\MailRecipientStruct;
use Shopware\Core\Framework\Event\EventData\ScalarValueType;
use Shopware\Core\Framework\Event\FlowEventAware;
use Shopware\Core\Framework\Event\MailAware;
use Symfony\Contracts\EventDispatcher\Event;

class DeleteAbandonedCartEvent extends Event implements MailAware, ScalarValuesAware, FlowEventAware
{
    public const EVENT_NAME = 'dumka.abandoned_cart.deleted';

    private $abandoned_cart;
    private $mailRecipientStruct;
    private Context $context;

    public function __construct(Context $context, AbandonedCartEntity $entity)
    {
        $this->abandoned_cart = $entity;
        $this->context = $context;
    }

    public function getAbandonedCart(): AbandonedCartEntity
    {
        return $this->abandoned_cart;
    }

    public function getName(): string
    {
        return self::EVENT_NAME;
    }

    public static function getAvailableData(): EventDataCollection
    {
        return (new EventDataCollection())
            ->add('abandoned_cart', new EntityType(AbandonedCartDefinition::class));
    }

    public function getContext(): Context
    {
        return $this->context;
    }

    public function getValues(): array
    {
        return [
            'abandoned_cart' => $this->abandoned_cart,
        ];
    }

    public function getMailStruct(): MailRecipientStruct
    {
        if (!$this->mailRecipientStruct instanceof MailRecipientStruct) {
            $this->mailRecipientStruct = new MailRecipientStruct([]);
        }

        return $this->mailRecipientStruct;
    }

    public function getSalesChannelId(): string
    {
        return $this->abandoned_cart->getSalesChannelId();
    }
}
