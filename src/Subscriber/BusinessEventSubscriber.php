<?php declare(strict_types=1);
namespace MailCampaigns\AbandonedCart\Subscriber;


use MailCampaigns\AbandonedCart\Core\Event\DeleteAbandonedCartEvent;
use MailCampaigns\AbandonedCart\Core\Event\MarkAbandonedCartEvent;
use Shopware\Core\Framework\Event\BusinessEventCollector;
use Shopware\Core\Framework\Event\BusinessEventCollectorEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class BusinessEventSubscriber implements EventSubscriberInterface
{
    private BusinessEventCollector $businessEventCollector;

    public function __construct(BusinessEventCollector $businessEventCollector)
    {
        $this->businessEventCollector = $businessEventCollector;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            BusinessEventCollectorEvent::NAME => 'onAddBusinessEvents',
        ];
    }


    public function onAddBusinessEvents(BusinessEventCollectorEvent $event): void
    {
        $collection = $event->getCollection();
        $definitionCreatedEvent = $this->businessEventCollector->define(DeleteAbandonedCartEvent::class, DeleteAbandonedCartEvent::EVENT_NAME);
        if ($definitionCreatedEvent) {
            $collection->add($definitionCreatedEvent);
        }
        $definitionUpdatedEvent = $this->businessEventCollector->define(MarkAbandonedCartEvent::class, MarkAbandonedCartEvent::EVENT_NAME);
        if ($definitionUpdatedEvent) {
            $collection->add($definitionUpdatedEvent);
        }
    }
}

