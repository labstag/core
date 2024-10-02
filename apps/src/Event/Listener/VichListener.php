<?php

namespace Labstag\Event\Listener;

use Doctrine\ORM\EntityManagerInterface;
use Override;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Vich\UploaderBundle\Event\Event;
use Vich\UploaderBundle\Event\Events;

final class VichListener implements EventSubscriberInterface
{
    public function __construct(
        private RequestStack $requestStack,
        private readonly EntityManagerInterface $entityManager
    )
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
        $request = $this->requestStack->getCurrentRequest();
        $all = $request->request->all();
        $serialize = serialize($all);
        if ($filterCollection->isEnabled('deletedfile') || 1 == substr_count($serialize, '{s:6:"delete";s:1:"1";}')) {
            return;
        }

        $event->cancel();
    }
}
