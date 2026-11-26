<?php

namespace Labstag\EventSubscriber;

use Doctrine\ORM\EntityManagerInterface;
use Labstag\Entity\Messenger as MessengerEntity;
use Labstag\Messenger\Stamp\CorrelationIdStamp;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Messenger\Event\MessageSentToTransportsEvent;
use Symfony\Component\Messenger\Event\WorkerMessageFailedEvent;
use Symfony\Component\Messenger\Event\WorkerMessageHandledEvent;

class MessengerHistorySubscriber implements EventSubscriberInterface
{
    public function __construct(
        private EntityManagerInterface $em,
        private ?LoggerInterface $logger = null,
    ) {}

    public static function getSubscribedEvents(): array
    {
        return [
            MessageSentToTransportsEvent::class => 'onSent',
            WorkerMessageHandledEvent::class => 'onHandled',
            WorkerMessageFailedEvent::class => 'onFailed',
        ];
    }

    public function onSent(MessageSentToTransportsEvent $event): void
    {
        $stamp = $event->getEnvelope()->last(CorrelationIdStamp::class);
        $correlationId = $stamp instanceof CorrelationIdStamp ? $stamp->correlationId : null;
        foreach (array_keys($event->getSenders()) as $transport) {
            $existing = null;
            if ($correlationId) {
                $existing = $this->em->getRepository(MessengerEntity::class)->findOneBy([
                    'correlationId' => $correlationId,
                    'transport' => $transport,
                ]);
            }
            if ($existing) {
                continue;
            }
            $row = new MessengerEntity();
            if ($correlationId) {
                $row->setCorrelationId($correlationId);
            }
            $row->setTransport($transport);
            $row->setMessageClass(get_class($event->getEnvelope()->getMessage()));
            $row->setStatus(MessengerEntity::STATUS_PENDING);
            $this->em->persist($row);
        }
        $this->flush();
    }

    public function onHandled(WorkerMessageHandledEvent $event): void
    {
        $row = $this->findRow($event->getEnvelope());
        if (!$row) {
            return;
        }
        if (!$row->getStartedAt()) {
            $row->setStartedAt(new \DateTimeImmutable());
        }
        $row->setStatus(MessengerEntity::STATUS_SUCCESS);
        $row->setFinishedAt(new \DateTimeImmutable());
        if ($row->getStartedAt()) {
            $row->setDurationMs((int) ((microtime(true) - $row->getStartedAt()->getTimestamp()) * 1000));
        }
        $row->setExceptionClass(null);
        $row->setExceptionMessage(null);
        $now = new \DateTimeImmutable();
        $row->setExpiresAt($now->modify('+7 days'));
        $this->flush();
    }

    public function onFailed(WorkerMessageFailedEvent $event): void
    {
        $row = $this->findRow($event->getEnvelope());
        if (!$row) {
            return;
        }
        if (!$row->getStartedAt()) {
            $row->setStartedAt(new \DateTimeImmutable());
        }
        $row->setStatus(MessengerEntity::STATUS_FAILED);
        $row->setExceptionClass(get_class($event->getThrowable()));
        $row->setExceptionMessage($event->getThrowable()->getMessage());
        $row->incrementRetryCount();
        $this->flush();
    }

    private function findRow(\Symfony\Component\Messenger\Envelope $envelope): ?MessengerEntity
    {
        $stamp = $envelope->last(CorrelationIdStamp::class);
        $correlationId = $stamp instanceof CorrelationIdStamp ? $stamp->correlationId : null;
        if ($correlationId) {
            $row = $this->em->getRepository(MessengerEntity::class)->findOneBy([
                'correlationId' => $correlationId,
            ]);
            if ($row) {
                return $row;
            }
        }

        return null;
    }

    private function flush(): void
    {
        try {
            $this->em->flush();
        } catch (\Throwable $e) {
            if ($this->logger) {
                $this->logger->error($e->getMessage());
            }
        }
    }
}