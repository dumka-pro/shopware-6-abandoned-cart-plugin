<?php
namespace MailCampaigns\AbandonedCart\Core\Event;

use Shopware\Core\Content\Flow\Dispatching\Aware\ScalarValuesAware;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\Event\EventData\EventDataCollection;
use Shopware\Core\Framework\Event\EventData\MailRecipientStruct;
use Shopware\Core\Framework\Event\EventData\ScalarValueType;
use Shopware\Core\Framework\Event\FlowEventAware;
use Shopware\Core\Framework\Event\MailAware;
use Symfony\Contracts\EventDispatcher\Event;

class DeleteAbandonedCartEvent extends Event implements MailAware, ScalarValuesAware,FlowEventAware
{
    public const EVENT_NAME = 'dumka.abandoned_cart.deleted';

    private $cartId;
    private $mailRecipientStruct;

    private Context $context;

    public function __construct(string $cartId, Context $context)
    {
        $this->cartId = $cartId;
        $this->context = $context;
    }


    public function getCartId(): string
    {
        return $this->cartId;
    }

    public function getName(): string
    {
        return self::EVENT_NAME;
    }

    public static function getAvailableData(): EventDataCollection
    {
        return (new EventDataCollection())
            ->add('cartId', new ScalarValueType('string'));
    }

    public function getContext(): Context
    {
        return $this->context;
    }

    public function getValues(): array
    {
        return [
            'cartId' => $this->cartId,
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
        //TODO: get sales channel id from abbandoned cart
        return '';
    }
}