<?php

namespace Labstag\Event\Listener;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;

final class AdminListener
{
    public function __construct(
        private EntityManagerInterface $entityManager,
    )
    {
    }

    #[AsEventListener(event: KernelEvents::REQUEST)]
    public function disableFilterSoftDeleteable(RequestEvent $requestEvent): void
    {
        $filterCollection = $this->entityManager->getFilters();
        if ($this->enableDeleteFile($requestEvent)) {
            $filterCollection->enable('deletedfile');
        }

        if ($this->isDelete($requestEvent)) {
            $filterCollection->disable('softdeleteable');
        }
    }

    private function enableDeleteFile(RequestEvent $requestEvent): bool
    {
        $request = $requestEvent->getRequest();
        $all = $request->request->all();
        $serialize = serialize($all);

        return substr_count($serialize, '{s:6:"delete";s:1:"1";}') == 1;
    }

    private function isDelete(RequestEvent $requestEvent): bool
    {
        $request = $requestEvent->getRequest();
        $crudAction = $request->query->get('crudAction', null);
        $action = $request->query->get('action', null);
        $referer = $request->headers->get('referer', null);
        if ($action == 'trash') {
            return true;
        }

        if ($crudAction != 'batchDelete') {
            return false;
        }

        return substr_count((string) $referer, 'action=trash') == 1;
    }
}
