<?php
namespace MailCampaigns\AbandonedCart\Core\Event;

use MailCampaigns\AbandonedCart\Core\Checkout\AbandonedCart\AbandonedCartEntity;
use Shopware\Core\Content\Flow\Dispatching\Aware\ScalarValuesAware;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\Event\EventData\ArrayType;
use Shopware\Core\Framework\Event\EventData\EventDataCollection;
use Shopware\Core\Framework\Event\EventData\MailRecipientStruct;
use Shopware\Core\Framework\Event\EventData\ScalarValueType;
use Shopware\Core\Framework\Event\FlowEventAware;
use Shopware\Core\Framework\Event\MailAware;
use Symfony\Contracts\EventDispatcher\Event;

class MarkAbandonedCartEvent extends Event implements MailAware,ScalarValuesAware,FlowEventAware
{
    public const EVENT_NAME = 'dumka.abandoned_cart.marked';

    private $abandoned_cart;

    private $customer;
    private $mailRecipientStruct;
    private Context $context;

    public function __construct(Context $context,array $cart)
    {
        $this->abandoned_cart = $cart;
        $this->context = $context;
    }

    /**
     * @return AbandonedCartEntity
     */
    public function getAbandonedCart(): array
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
            ->add('cart', new ArrayType(new ScalarValueType(ScalarValueType::TYPE_STRING)));
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
        return $this->abandoned_cart['sales_channel_id'];
    }
}