<?php

declare(strict_types=1);

namespace Labstag\MessengerInspector\Transport;

interface TransportAdapterInterface
{
    public function delete(string $id): bool;

    /**
     * @return array<int, mixed>
     */
    public function getFailedMessages(int $limit = 50): array;

    public function getInfo(): array;

    /**
     * @return array<int, mixed>
     */
    public function getMessages(int $limit = 50): array;

    public function getName(): string;

    public function getPendingCount(): ?int;

    public function getType(): string;

    public function retry(string $id): bool;

    public function supports(string $capability): bool;
}
