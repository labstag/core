<?php

namespace Labstag\Entity;

use Doctrine\DBAL\Types\Types;
use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Uid\Ulid;

#[ORM\Entity]
#[ORM\Table(name: 'messenger')]
#[ORM\Index(name: 'idx_messenger_correlation', columns: ['correlation_id'])]
#[ORM\Index(name: 'idx_messenger_transport', columns: ['transport'])]
#[ORM\Index(name: 'idx_messenger_message_class', columns: ['message_class'])]
#[ORM\Index(name: 'idx_messenger_status', columns: ['status'])]
#[ORM\Index(name: 'idx_messenger_dispatched', columns: ['dispatched_at'])]
#[ORM\Index(name: 'idx_messenger_expires', columns: ['expires_at'])]
class Messenger
{
    public const STATUS_FAILED = 'failed';

    public const STATUS_PENDING = 'pending';

    public const STATUS_PROCESSING = 'processing';

    public const STATUS_SUCCESS = 'success';

    #[ORM\Column(name: 'correlation_id', type: Types::STRING, length: 26)]
    private string $correlationId;

    #[ORM\Column(name: 'dispatched_at', type: Types::DATETIME_IMMUTABLE)]
    private DateTimeImmutable $dispatchedAt;

    #[ORM\Column(name: 'duration_ms', type: Types::INTEGER, nullable: true)]
    private ?int $durationMs = null;

    #[ORM\Column(name: 'exception_class', type: Types::STRING, length: 255, nullable: true)]
    private ?string $exceptionClass = null;

    #[ORM\Column(name: 'exception_message', type: Types::TEXT, nullable: true)]
    private ?string $exceptionMessage = null;

    #[ORM\Column(name: 'expires_at', type: Types::DATETIME_IMMUTABLE, nullable: true)]
    private ?DateTimeImmutable $expiresAt = null;

    #[ORM\Column(name: 'finished_at', type: Types::DATETIME_IMMUTABLE, nullable: true)]
    private ?DateTimeImmutable $finishedAt = null;

    #[ORM\Column(type: Types::JSON, nullable: true)]
    private ?array $headers = null;

    #[ORM\Id]
    #[ORM\Column(type: Types::STRING, length: 26)]
    private string $id;

    #[ORM\Column(name: 'message_class', type: Types::STRING, length: 255)]
    private string $messageClass;

    #[ORM\Column(type: Types::JSON, nullable: true)]
    private ?array $payload = null;

    #[ORM\Column(name: 'retry_count', type: Types::INTEGER)]
    private int $retryCount = 0;

    #[ORM\Column(name: 'started_at', type: Types::DATETIME_IMMUTABLE, nullable: true)]
    private ?DateTimeImmutable $startedAt = null;

    #[ORM\Column(type: Types::STRING, length: 16)]
    private string $status = self::STATUS_PENDING;

    #[ORM\Column(type: Types::STRING, length: 64)]
    private string $transport;

    public function __construct()
    {
        $this->id            = (string) new Ulid();
        $this->correlationId = (string) new Ulid();
        $this->dispatchedAt  = new DateTimeImmutable();
    }

    public function getCorrelationId(): string
    {
        return $this->correlationId;
    }

    public function getDispatchedAt(): DateTimeImmutable
    {
        return $this->dispatchedAt;
    }

    public function getDurationMs(): ?int
    {
        return $this->durationMs;
    }

    public function getExceptionClass(): ?string
    {
        return $this->exceptionClass;
    }

    public function getExceptionMessage(): ?string
    {
        return $this->exceptionMessage;
    }

    public function getExpiresAt(): ?DateTimeImmutable
    {
        return $this->expiresAt;
    }

    public function getFinishedAt(): ?DateTimeImmutable
    {
        return $this->finishedAt;
    }

    public function getHeaders(): ?array
    {
        return $this->headers;
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getMessageClass(): string
    {
        return $this->messageClass;
    }

    public function getPayload(): ?array
    {
        return $this->payload;
    }

    public function getRetryCount(): int
    {
        return $this->retryCount;
    }

    public function getStartedAt(): ?DateTimeImmutable
    {
        return $this->startedAt;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function getTransport(): string
    {
        return $this->transport;
    }

    public function incrementRetryCount(): self
    {
        $this->retryCount++;
        return $this;
    }

    public function setCorrelationId(string $correlationId): self
    {
        $this->correlationId = $correlationId;
        return $this;
    }

    public function setDispatchedAt(DateTimeImmutable $dispatchedAt): self
    {
        $this->dispatchedAt = $dispatchedAt;
        return $this;
    }

    public function setDurationMs(?int $durationMs): self
    {
        $this->durationMs = $durationMs;
        return $this;
    }

    public function setExceptionClass(?string $exceptionClass): self
    {
        $this->exceptionClass = $exceptionClass;
        return $this;
    }

    public function setExceptionMessage(?string $exceptionMessage): self
    {
        $this->exceptionMessage = $exceptionMessage;
        return $this;
    }

    public function setExpiresAt(?DateTimeImmutable $expiresAt): self
    {
        $this->expiresAt = $expiresAt;
        return $this;
    }

    public function setFinishedAt(?DateTimeImmutable $finishedAt): self
    {
        $this->finishedAt = $finishedAt;
        return $this;
    }

    public function setHeaders(?array $headers): self
    {
        $this->headers = $headers;
        return $this;
    }

    public function setMessageClass(string $messageClass): self
    {
        $this->messageClass = $messageClass;
        return $this;
    }

    public function setPayload(?array $payload): self
    {
        $this->payload = $payload;
        return $this;
    }

    public function setRetryCount(int $retryCount): self
    {
        $this->retryCount = $retryCount;
        return $this;
    }

    public function setStartedAt(?DateTimeImmutable $startedAt): self
    {
        $this->startedAt = $startedAt;
        return $this;
    }

    public function setStatus(string $status): self
    {
        $this->status = $status;
        return $this;
    }

    public function setTransport(string $transport): self
    {
        $this->transport = $transport;
        return $this;
    }
}
