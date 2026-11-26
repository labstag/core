<?php

namespace Labstag\EventSubscriber;

use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Messenger\Event\WorkerMessageFailedEvent;
use Symfony\Component\Messenger\Event\WorkerMessageHandledEvent;
use Symfony\Component\Messenger\Event\WorkerStartedEvent;
use Symfony\Component\Messenger\Event\WorkerStoppedEvent;

class WorkerSupervisionSubscriber implements EventSubscriberInterface
{
    public function __construct(private CacheInterface $cache) {}

    public static function getSubscribedEvents(): array
    {
        return [
            WorkerStartedEvent::class => 'onStarted',
            WorkerStoppedEvent::class => 'onStopped',
            WorkerMessageHandledEvent::class => 'onHandled',
            WorkerMessageFailedEvent::class => 'onFailed',
        ];
    }

    public function onStarted(WorkerStartedEvent $event): void
    {
        $this->cache->get('worker_' . spl_object_id($event->getWorker()), fn () => [
            'startedAt' => time(),
            'handled' => 0,
            'failed' => 0,
        ]);
    }

    public function onStopped(WorkerStoppedEvent $event): void
    {
        // passive: nothing to do
    }

    public function onHandled(WorkerMessageHandledEvent $event): void
    {
    }

    public function onFailed(WorkerMessageFailedEvent $event): void
    {
    }
}