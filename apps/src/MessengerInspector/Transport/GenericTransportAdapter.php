<?php

declare(strict_types=1);

namespace Labstag\MessengerInspector\Transport;

class GenericTransportAdapter implements TransportAdapterInterface
{
    public function __construct(
        private string $name,
        private string $type
    )
    {
    }

    public function delete(string $id): bool
    {
        return false;
    }

    public function getFailedMessages(int $limit = 50): array
    {
        return [];
    }

    public function getInfo(): array
    {
        return [
            'name' => $this->name,
            'type' => $this->type,
        ];
    }

    public function getMessages(int $limit = 50): array
    {
        return [];
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getPendingCount(): ?int
    {
        return null;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function retry(string $id): bool
    {
        return false;
    }

    public function supports(string $capability): bool
    {
        return false;
    }
}
