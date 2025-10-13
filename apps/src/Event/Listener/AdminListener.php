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
        $request     = $requestEvent->getRequest();
        $deleteParam = $request->request->get('delete');

        return '1' === $deleteParam;
    }

    private function isDelete(RequestEvent $requestEvent): bool
    {
        $request    = $requestEvent->getRequest();
        $crudAction = $request->query->get('crudAction', null);
        $action     = $request->query->get('action', null);
        $referer    = $request->headers->get('referer', null);
        if ('trash' == $action) {
            return true;
        }

        if ('batchDelete' != $crudAction) {
            return false;
        }

        return 1 === substr_count((string) $referer, 'action=trash');
    }
}
