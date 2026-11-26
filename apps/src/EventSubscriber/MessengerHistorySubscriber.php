<?php

namespace Labstag\EventSubscriber;

use JsonSerializable;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Labstag\Entity\Messenger as MessengerEntity;
use Labstag\Messenger\Stamp\CorrelationIdStamp;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Event\MessageSentToTransportsEvent;
use Symfony\Component\Messenger\Event\WorkerMessageFailedEvent;
use Symfony\Component\Messenger\Event\WorkerMessageHandledEvent;
use Throwable;

class MessengerHistorySubscriber implements EventSubscriberInterface
{
    public function __construct(
        private EntityManagerInterface $em,
        private ?LoggerInterface $logger = null,
    )
    {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            MessageSentToTransportsEvent::class => 'onSent',
            WorkerMessageHandledEvent::class    => 'onHandled',
            WorkerMessageFailedEvent::class     => 'onFailed',
        ];
    }

    public function onFailed(WorkerMessageFailedEvent $event): void
    {
        $row = $this->findRow($event->getEnvelope());
        if (!$row instanceof MessengerEntity) {
            return;
        }

        if (!$row->getStartedAt() instanceof DateTimeImmutable) {
            $row->setStartedAt(new DateTimeImmutable());
        }

        $row->setStatus(MessengerEntity::STATUS_FAILED);
        $row->setExceptionClass($event->getThrowable()::class);
        $row->setExceptionMessage($event->getThrowable()->getMessage());
        $row->incrementRetryCount();
        $this->flush();
    }

    public function onHandled(WorkerMessageHandledEvent $event): void
    {
        $row = $this->findRow($event->getEnvelope());
        if (!$row instanceof MessengerEntity) {
            return;
        }

        if (!$row->getStartedAt() instanceof DateTimeImmutable) {
            $row->setStartedAt(new DateTimeImmutable());
        }

        $row->setStatus(MessengerEntity::STATUS_SUCCESS);
        $row->setFinishedAt(new DateTimeImmutable());
        if ($row->getStartedAt() instanceof DateTimeImmutable) {
            $row->setDurationMs((int) ((microtime(true) - $row->getStartedAt()->getTimestamp()) * 1000));
        }

        $row->setExceptionClass(null);
        $row->setExceptionMessage(null);

        $now = new DateTimeImmutable();
        $row->setExpiresAt($now->modify('+7 days'));
        $this->flush();
    }

    public function onSent(MessageSentToTransportsEvent $event): void
    {
        $stamp         = $event->getEnvelope()->last(CorrelationIdStamp::class);
        $correlationId = $stamp instanceof CorrelationIdStamp ? $stamp->correlationId : null;
        $transports    = array_keys($event->getSenders());
        $existing      = null;
        if ($correlationId) {
            $existing = $this->em->getRepository(MessengerEntity::class)->findOneBy(
                ['correlationId' => $correlationId]
            );
        }

        if ($existing instanceof MessengerEntity) {
            $merged = array_values(array_unique(array_merge($existing->getTransports() ?? [], $transports)));
            $existing->setTransports($merged);
            $this->flush();
            return;
        }

        $row = new MessengerEntity();
        if ($correlationId) {
            $row->setCorrelationId($correlationId);
        }

        $row->setTransports($transports);
        $row->setMessageClass($event->getEnvelope()->getMessage()::class);
        $row->setStatus(MessengerEntity::STATUS_PENDING);

        $message = $event->getEnvelope()->getMessage();
        $row->setPayload($this->mask($this->toArray($message)));
        $row->setHeaders($this->mask($this->headers($event->getEnvelope())));

        $this->em->persist($row);
        $this->flush();
    }

    private function findRow(Envelope $envelope): ?MessengerEntity
    {
        $stamp         = $envelope->last(CorrelationIdStamp::class);
        $correlationId = $stamp instanceof CorrelationIdStamp ? $stamp->correlationId : null;
        if ($correlationId) {
            $row = $this->em->getRepository(MessengerEntity::class)->findOneBy(
                ['correlationId' => $correlationId]
            );
            if ($row instanceof MessengerEntity) {
                return $row;
            }
        }

        return null;
    }

    private function flush(): void
    {
        try {
            $this->em->flush();
        } catch (Throwable $throwable) {
            if ($this->logger instanceof LoggerInterface) {
                $this->logger->error($throwable->getMessage());
            }
        }
    }

    private function headers(Envelope $envelope): array
    {
        $result = [];
        foreach ($envelope->all() as $stamps) {
            foreach ($stamps as $stamp) {
                $result[] = $stamp::class;
            }
        }

        return $result;
    }

    private function mask(mixed $value): mixed
    {
        if (is_array($value)) {
            $out = [];
            foreach ($value as $k => $v) {
                if (is_string($k) && preg_match('/pass|token|secret|key/i', $k)) {
                    $out[$k] = '***';
                } else {
                    $out[$k] = $this->mask($v);
                }
            }

            return $out;
        }

        return $value;
    }

    private function toArray(mixed $value): mixed
    {
        if (is_object($value)) {
            if (method_exists($value, 'toArray')) {
                return $value->toArray();
            }

            if ($value instanceof JsonSerializable) {
                return $value->jsonSerialize();
            }

            return get_object_vars($value);
        }

        return $value;
    }
}
