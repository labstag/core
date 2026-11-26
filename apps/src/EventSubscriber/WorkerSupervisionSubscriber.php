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
        $worker = $event->getWorker();
        $key    = $this->key($worker);
        $data   = $this->cache->get(
            $key,
            fn (): array => [
                'startedAt' => time(),
                'handled'   => 0,
                'failed'    => 0,
                'stopped'   => false,
            ]
        );
        $data['failed']++;
        $data['lastActivityAt'] = time();
        $this->cache->delete($key);
        $this->cache->get($key, fn (): array => $data);
    }

    public function onHandled(WorkerMessageHandledEvent $event): void
    {
        $worker = $event->getWorker();
        $key    = $this->key($worker);
        $data   = $this->cache->get(
            $key,
            fn (): array => [
                'startedAt' => time(),
                'handled'   => 0,
                'failed'    => 0,
                'stopped'   => false,
            ]
        );
        $data['handled']++;
        $data['lastActivityAt'] = time();
        $this->cache->delete($key);
        $this->cache->get($key, fn (): array => $data);
    }

    public function onStarted(WorkerStartedEvent $event): void
    {
        $worker = $event->getWorker();
        $key    = $this->key($worker);
        $this->cache->get(
            $key,
            fn (): array => [
                'startedAt'      => time(),
                'handled'        => 0,
                'failed'         => 0,
                'stopped'        => false,
                'lastActivityAt' => time(),
            ]
        );
    }

    public function onStopped(WorkerStoppedEvent $event): void
    {
        $worker = $event->getWorker();
        $key    = $this->key($worker);
        $data   = $this->cache->get(
            $key,
            fn (): array => [
                'startedAt' => time(),
                'handled'   => 0,
                'failed'    => 0,
                'stopped'   => false,
            ]
        );
        $data['stopped']   = true;
        $data['stoppedAt'] = time();
        $this->cache->delete($key);
        $this->cache->get($key, fn (): array => $data);
    }

    private function key(object $worker): string
    {
        return 'worker_' . getmypid() . '_' . spl_object_id($worker);
    }
}
