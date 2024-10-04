<?php

namespace Labstag\Event\Listener;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;

final class AdminListener
{
    public function __construct(
        protected EntityManagerInterface $entityManager
    )
    {
    }

    #[AsEventListener(event: KernelEvents::REQUEST)]
    public function disableFilterSoftDeleteable(RequestEvent $requestEvent): void
    {
        $request          = $requestEvent->getRequest();
        $action           = $request->query->get('action', null);
        $all              = $request->request->all();
        $serialize        = serialize($all);
        $filterCollection = $this->entityManager->getFilters();
        if (1 == substr_count($serialize, '{s:6:"delete";s:1:"1";}')) {
            $filterCollection->enable('deletedfile');
        }

        if ('trash' == $action) {
            $filterCollection->disable('softdeleteable');
        }
    }
}
