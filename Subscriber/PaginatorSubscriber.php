<?php

namespace Diside\SecurityBundle\Subscriber;

use Knp\Component\Pager\Event\ItemsEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class PaginatorSubscriber implements EventSubscriberInterface
{
    public function items(ItemsEvent $event)
    {
        $presenter = $event->target;

        $event->count = $presenter->count();
        $event->items = $presenter->getItems();
        $event->stopPropagation();
    }

    public static function getSubscribedEvents()
    {
        return array(
            'knp_pager.items' => array('items', 1)
        );
    }
} 