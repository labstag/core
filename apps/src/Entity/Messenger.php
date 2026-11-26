<?php

namespace Labstag\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Uid\Ulid;

#[ORM\Entity]
#[ORM\Table(name: 'messenger')]
#[ORM\Index(columns: ['correlation_id'], name: 'idx_messenger_correlation')]
#[ORM\Index(columns: ['transport'], name: 'idx_messenger_transport')]
#[ORM\Index(columns: ['message_class'], name: 'idx_messenger_message_class')]
#[ORM\Index(columns: ['status'], name: 'idx_messenger_status')]
#[ORM\Index(columns: ['dispatched_at'], name: 'idx_messenger_dispatched')]
#[ORM\Index(columns: ['expires_at'], name: 'idx_messenger_expires')]
class Messenger
{
    public const STATUS_PENDING = 'pending';
    public const STATUS_PROCESSING = 'processing';
    public const STATUS_SUCCESS = 'success';
    public const STATUS_FAILED = 'failed';

    #[ORM\Id]
    #[ORM\Column(type: 'string', length: 26)]
    private string $id;

    #[ORM\Column(name: 'correlation_id', type: 'string', length: 26)]
    private string $correlationId;

    #[ORM\Column(type: 'string', length: 64)]
    private string $transport;

    #[ORM\Column(name: 'message_class', type: 'string', length: 255)]
    private string $messageClass;

    #[ORM\Column(type: 'string', length: 16)]
    private string $status = self::STATUS_PENDING;

    #[ORM\Column(type: 'json', nullable: true)]
    private ?array $payload = null;

    #[ORM\Column(type: 'json', nullable: true)]
    private ?array $headers = null;

    #[ORM\Column(name: 'retry_count', type: 'integer')]
    private int $retryCount = 0;

    #[ORM\Column(name: 'dispatched_at', type: 'datetime_immutable')]
    private \DateTimeImmutable $dispatchedAt;

    #[ORM\Column(name: 'started_at', type: 'datetime_immutable', nullable: true)]
    private ?\DateTimeImmutable $startedAt = null;

    #[ORM\Column(name: 'finished_at', type: 'datetime_immutable', nullable: true)]
    private ?\DateTimeImmutable $finishedAt = null;

    #[ORM\Column(name: 'duration_ms', type: 'integer', nullable: true)]
    private ?int $durationMs = null;

    #[ORM\Column(name: 'exception_class', type: 'string', length: 255, nullable: true)]
    private ?string $exceptionClass = null;

    #[ORM\Column(name: 'exception_message', type: 'text', nullable: true)]
    private ?string $exceptionMessage = null;

    #[ORM\Column(name: 'expires_at', type: 'datetime_immutable', nullable: true)]
    private ?\DateTimeImmutable $expiresAt = null;

    public function __construct()
    {
        $this->id = (string) new Ulid();
        $this->correlationId = (string) new Ulid();
        $this->dispatchedAt = new \DateTimeImmutable();
    }

    public function getId(): string { return $this->id; }
    public function getCorrelationId(): string { return $this->correlationId; }
    public function setCorrelationId(string $correlationId): self { $this->correlationId = $correlationId; return $this; }
    public function getTransport(): string { return $this->transport; }
    public function setTransport(string $transport): self { $this->transport = $transport; return $this; }
    public function getMessageClass(): string { return $this->messageClass; }
    public function setMessageClass(string $messageClass): self { $this->messageClass = $messageClass; return $this; }
    public function getStatus(): string { return $this->status; }
    public function setStatus(string $status): self { $this->status = $status; return $this; }
    public function getPayload(): ?array { return $this->payload; }
    public function setPayload(?array $payload): self { $this->payload = $payload; return $this; }
    public function getHeaders(): ?array { return $this->headers; }
    public function setHeaders(?array $headers): self { $this->headers = $headers; return $this; }
    public function getRetryCount(): int { return $this->retryCount; }
    public function setRetryCount(int $retryCount): self { $this->retryCount = $retryCount; return $this; }
    public function incrementRetryCount(): self { $this->retryCount++; return $this; }
    public function getDispatchedAt(): \DateTimeImmutable { return $this->dispatchedAt; }
    public function setDispatchedAt(\DateTimeImmutable $dispatchedAt): self { $this->dispatchedAt = $dispatchedAt; return $this; }
    public function getStartedAt(): ?\DateTimeImmutable { return $this->startedAt; }
    public function setStartedAt(?\DateTimeImmutable $startedAt): self { $this->startedAt = $startedAt; return $this; }
    public function getFinishedAt(): ?\DateTimeImmutable { return $this->finishedAt; }
    public function setFinishedAt(?\DateTimeImmutable $finishedAt): self { $this->finishedAt = $finishedAt; return $this; }
    public function getDurationMs(): ?int { return $this->durationMs; }
    public function setDurationMs(?int $durationMs): self { $this->durationMs = $durationMs; return $this; }
    public function getExceptionClass(): ?string { return $this->exceptionClass; }
    public function setExceptionClass(?string $exceptionClass): self { $this->exceptionClass = $exceptionClass; return $this; }
    public function getExceptionMessage(): ?string { return $this->exceptionMessage; }
    public function setExceptionMessage(?string $exceptionMessage): self { $this->exceptionMessage = $exceptionMessage; return $this; }
    public function getExpiresAt(): ?\DateTimeImmutable { return $this->expiresAt; }
    public function setExpiresAt(?\DateTimeImmutable $expiresAt): self { $this->expiresAt = $expiresAt; return $this; }
}