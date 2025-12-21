<?php

namespace Labstag\EventListener;

use Labstag\Service\MessageDispatcherService;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * Listener qui réinitialise le cache des messages dispatchés à chaque nouvelle requête.
 */
#[AsEventListener(event: KernelEvents::REQUEST, priority: 1024)]
final class MessageDispatcherResetListener
{
    public function __construct(
        private MessageDispatcherService $messageDispatcherService,
    )
    {
    }

    public function __invoke(RequestEvent $event): void
    {
        if (!$event->isMainRequest()) {
            return;
        }

        $this->messageDispatcherService->reset();
    }
}
