<?php

namespace Labstag\Event\Listener;

use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Vich\UploaderBundle\Event\Event;
use Vich\UploaderBundle\Event\Events;

final class VichListener
{
    #[AsEventListener(event: Events::PRE_REMOVE)]
    public function preRemove(Event $event): void
    {
        $object  = $event->getObject();
        $methods = get_class_methods($object);
        if (in_array('getDeletedAt', $methods) && null != $object->getDeletedAt()) {
            return;
        }

        $event->cancel();
    }
}
