<?php

namespace Labstag\Event\Subscriber;

use EasyCorp\Bundle\EasyAdminBundle\Event\AfterEntityPersistedEvent;
use EasyCorp\Bundle\EasyAdminBundle\Event\AfterEntityUpdatedEvent;
use EasyCorp\Bundle\EasyAdminBundle\Event\BeforeEntityPersistedEvent;
use EasyCorp\Bundle\EasyAdminBundle\Event\BeforeEntityUpdatedEvent;
use Labstag\Lib\EventEntityLib;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class EasyadminSubscriber extends EventEntityLib implements EventSubscriberInterface
{
    public function beforePersisted(BeforeEntityPersistedEvent $beforeEntityPersistedEvent): void
    {
        $instance = $beforeEntityPersistedEvent->getEntityInstance();
        $this->initworkflow($instance);
        $this->initEntityMeta($instance);
    }

    public function beforeUpdated(BeforeEntityUpdatedEvent $beforeEntityUpdatedEvent): void
    {
        $beforeEntityUpdatedEvent->getEntityInstance();
    }

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

    public static function getSubscribedEvents()
    {
        return [
            BeforeEntityPersistedEvent::class => ['beforePersisted'],
            BeforeEntityUpdatedEvent::class   => ['beforeUpdated'],
            AfterEntityPersistedEvent::class  => ['afterPersisted'],
            AfterEntityUpdatedEvent::class    => ['afterUpdated'],
        ];
    }
}
