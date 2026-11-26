<?php

declare(strict_types=1);

namespace Labstag\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Messenger\Event\WorkerMessageFailedEvent;
use Symfony\Component\Messenger\Event\WorkerMessageHandledEvent;
use Symfony\Component\Messenger\Event\WorkerStartedEvent;
use Symfony\Component\Messenger\Event\WorkerStoppedEvent;
use Symfony\Contracts\Cache\CacheInterface;

class WorkerSupervisionSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private CacheInterface $cache
    )
    {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            WorkerStartedEvent::class        => 'onStarted',
            WorkerStoppedEvent::class        => 'onStopped',
            WorkerMessageHandledEvent::class => 'onHandled',
            WorkerMessageFailedEvent::class  => 'onFailed',
        ];
    }

    public function onFailed(WorkerMessageFailedEvent $event): void
    {
    }

    public function onHandled(WorkerMessageHandledEvent $event): void
    {
    }

    public function onStarted(WorkerStartedEvent $event): void
    {
        $this->cache->get(
            'worker_' . spl_object_id($event->getWorker()),
            fn (): array => [
                'startedAt' => time(),
                'handled'   => 0,
                'failed'    => 0,
            ]
        );
    }

    public function onStopped(WorkerStoppedEvent $event): void
    {
        // passive: nothing to do
    }
}
