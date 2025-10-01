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
    public function afterPersisted(AfterEntityPersistedEvent $afterEntityPersistedEvent): void
    {
        $instance = $afterEntityPersistedEvent->getEntityInstance();
        $this->updateEntityParagraph($instance);
        $this->updateEntityBlock($instance);
        $this->updateEntityBanIp($instance, $this->entityManager);
        $this->updateEntityStory($instance);
        $this->updateEntityMovie($instance);
        $this->updateEntityChapter($instance);
        $this->updateEntityPage($instance);
        $this->updateEntityRedirection($instance, $this->entityManager);

        $this->entityManager->flush();
    }

    public function afterUpdated(AfterEntityUpdatedEvent $afterEntityUpdatedEvent): void
    {
        $instance = $afterEntityUpdatedEvent->getEntityInstance();
        $this->updateEntityParagraph($instance);
        $this->updateEntityBlock($instance);
        $this->updateEntityBanIp($instance, $this->entityManager);
        $this->updateEntityStory($instance);
        $this->updateEntityMovie($instance);
        $this->updateEntityChapter($instance);
        $this->updateEntityPage($instance);
        $this->updateEntityRedirection($instance, $this->entityManager);

        $this->entityManager->flush();
    }

    public function beforePersisted(BeforeEntityPersistedEvent $beforeEntityPersistedEvent): void
    {
        $instance = $beforeEntityPersistedEvent->getEntityInstance();
        $this->initworkflow($instance);
        $this->initEntityMeta($instance);
        $this->updateEntityRedirection($instance, $this->entityManager);
    }

    public function beforeUpdated(BeforeEntityUpdatedEvent $beforeEntityUpdatedEvent): void
    {
        $instance = $beforeEntityUpdatedEvent->getEntityInstance();
        $this->updateEntityRedirection($instance, $this->entityManager);
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
