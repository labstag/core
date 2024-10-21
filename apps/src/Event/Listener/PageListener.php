<?php

namespace Labstag\Event\Listener;

use EasyCorp\Bundle\EasyAdminBundle\Event\AfterEntityUpdatedEvent;
use EasyCorp\Bundle\EasyAdminBundle\Event\BeforeEntityUpdatedEvent;
use Labstag\Entity\Page;
use Labstag\Repository\PageRepository;
use Override;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

final class PageListener implements EventSubscriberInterface
{
    public function __construct(
        protected PageRepository $pageRepository
    )
    {
    }

    public function afterUpdated($event)
    {
        $entity = $event->getEntityInstance();
        if (!$entity instanceof Page || !$entity->isHome()) {
            return;
        }

        $entity->setSlug('');
        $this->pageRepository->save($entity);
    }

    public function beforeUpdated(BeforeEntityUpdatedEvent $beforeEntityUpdatedEvent)
    {
        $entity = $beforeEntityUpdatedEvent->getEntityInstance();
        if (!$entity instanceof Page || !$entity->isHome()) {
            return;
        }

        $oldHome = $this->pageRepository->findOneBy(['home' => true]);
        if ($oldHome instanceof Page && $oldHome->getId() === $entity->getId()) {
            return;
        }

        if ($oldHome instanceof Page) {
            $oldHome->setHome(false);
            $this->pageRepository->save($oldHome);
        }

        $entity->setSlug('');
    }

    #[Override]
    public static function getSubscribedEvents()
    {
        return [
            AfterEntityUpdatedEvent::class  => ['afterUpdated'],
            BeforeEntityUpdatedEvent::class => ['beforeUpdated'],
        ];
    }
}
