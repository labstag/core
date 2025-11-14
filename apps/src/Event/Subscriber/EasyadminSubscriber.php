<?php

namespace Labstag\Event\Subscriber;

use EasyCorp\Bundle\EasyAdminBundle\Event\BeforeEntityPersistedEvent;
use EasyCorp\Bundle\EasyAdminBundle\Event\BeforeEntityUpdatedEvent;
use Labstag\Event\Abstract\EventEntityLib;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;

class EasyadminSubscriber extends EventEntityLib
{
    #[AsEventListener(event: BeforeEntityPersistedEvent::class)]
    public function beforePersisted(BeforeEntityPersistedEvent $beforeEntityPersistedEvent): void
    {
        $instance = $beforeEntityPersistedEvent->getEntityInstance();
        $this->prePersistMethods($instance, $this->entityManager);
    }

    #[AsEventListener(event: BeforeEntityUpdatedEvent::class)]
    public function beforeUpdated(BeforeEntityUpdatedEvent $beforeEntityUpdatedEvent): void
    {
        $instance = $beforeEntityUpdatedEvent->getEntityInstance();
        $this->prePersistMethods($instance, $this->entityManager);
    }
}
