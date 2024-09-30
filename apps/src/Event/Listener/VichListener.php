<?php

namespace Labstag\Event\Listener;

use Doctrine\ORM\EntityManagerInterface;
use Override;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Vich\UploaderBundle\Event\Event;
use Vich\UploaderBundle\Event\Events;

final class VichListener implements EventSubscriberInterface
{
    public function __construct(private readonly EntityManagerInterface $entityManager)
    {
    }

    #[Override]
    public static function getSubscribedEvents()
    {
        return [Events::PRE_REMOVE => 'preRemove'];
    }

    public function preRemove(Event $event)
    {
        $filterCollection = $this->entityManager->getFilters();
        if ($filterCollection->isEnabled('deletedfile')) {
            return;
        }

        $event->cancel();
    }
}
