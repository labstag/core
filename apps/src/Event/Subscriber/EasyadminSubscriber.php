<?php

namespace Labstag\Event\Subscriber;

use EasyCorp\Bundle\EasyAdminBundle\Event\AfterEntityPersistedEvent;
use EasyCorp\Bundle\EasyAdminBundle\Event\AfterEntityUpdatedEvent;
use EasyCorp\Bundle\EasyAdminBundle\Event\BeforeEntityPersistedEvent;
use EasyCorp\Bundle\EasyAdminBundle\Event\BeforeEntityUpdatedEvent;
use Labstag\Event\Abstract\EventEntityLib;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class EasyadminSubscriber extends EventEntityLib implements EventSubscriberInterface
{
    /**
     * @param AfterEntityPersistedEvent<object> $afterEntityPersistedEvent
     */
    public function afterPersisted(AfterEntityPersistedEvent $afterEntityPersistedEvent): void
    {
        $instance = $afterEntityPersistedEvent->getEntityInstance();
        $this->postPersistMethods($instance, $this->entityManager);
    }

    /**
     * @param AfterEntityUpdatedEvent<object> $afterEntityUpdatedEvent
     */
    public function afterUpdated(AfterEntityUpdatedEvent $afterEntityUpdatedEvent): void
    {
        $instance = $afterEntityUpdatedEvent->getEntityInstance();
        $this->postPersistMethods($instance, $this->entityManager);
    }

    /**
     * @param BeforeEntityPersistedEvent<object> $beforeEntityPersistedEvent
     */
    public function beforePersisted(BeforeEntityPersistedEvent $beforeEntityPersistedEvent): void
    {
        $instance = $beforeEntityPersistedEvent->getEntityInstance();
        $this->prePersistMethods($instance, $this->entityManager);
    }

    /**
     * @param BeforeEntityUpdatedEvent<object> $beforeEntityUpdatedEvent
     */
    public function beforeUpdated(BeforeEntityUpdatedEvent $beforeEntityUpdatedEvent): void
    {
        $instance = $beforeEntityUpdatedEvent->getEntityInstance();
        $this->prePersistMethods($instance, $this->entityManager);
    }

    public static function getSubscribedEvents(): array
    {
        return [
            BeforeEntityPersistedEvent::class => ['beforePersisted'],
            BeforeEntityUpdatedEvent::class   => ['beforeUpdated'],
            AfterEntityPersistedEvent::class  => ['afterPersisted'],
            AfterEntityUpdatedEvent::class    => ['afterUpdated'],
        ];
    }
}
