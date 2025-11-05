<?php

namespace Labstag\Event\Subscriber;

use EasyCorp\Bundle\EasyAdminBundle\Event\AfterEntityPersistedEvent;
use EasyCorp\Bundle\EasyAdminBundle\Event\AfterEntityUpdatedEvent;
use EasyCorp\Bundle\EasyAdminBundle\Event\BeforeEntityPersistedEvent;
use EasyCorp\Bundle\EasyAdminBundle\Event\BeforeEntityUpdatedEvent;
use Labstag\Event\Abstract\EventEntityLib;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;

class EasyadminSubscriber extends EventEntityLib
{
    #[AsEventListener(event: AfterEntityPersistedEvent::class)]
    public function afterPersisted(AfterEntityPersistedEvent $afterEntityPersistedEvent): void
    {
        $instance = $afterEntityPersistedEvent->getEntityInstance();
        $this->postPersistMethods($instance, $this->entityManager);
    }

    #[AsEventListener(event: AfterEntityUpdatedEvent::class)]
    public function afterUpdated(AfterEntityUpdatedEvent $afterEntityUpdatedEvent): void
    {
        $instance = $afterEntityUpdatedEvent->getEntityInstance();
        $this->postPersistMethods($instance, $this->entityManager);
    }

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
