<?php

namespace Labstag\Service;

use Labstag\MessengerInspector\Transport\GenericTransportAdapter;
use Labstag\MessengerInspector\Transport\TransportRegistry;

class MessengerTransportDiscoveryService
{
    public function __construct(private TransportRegistry $registry) {}

    public function discover(array $transportConfig): void
    {
        foreach ($transportConfig as $name => $dsn) {
            $type = $this->detectType((string) $dsn);
            $caps = $this->capabilities($type);
            $this->registry->register(new GenericTransportAdapter($name, $type, $caps));
        }
    }

    private function detectType(string $dsn): string
    {
        if (str_starts_with($dsn, 'doctrine://')) return 'doctrine';
        if (str_starts_with($dsn, 'redis://')) return 'redis';
        if (str_starts_with($dsn, 'amqp://') || str_starts_with($dsn, 'amqps://')) return 'amqp';
        if (str_starts_with($dsn, 'sqs://')) return 'sqs';
        if ($dsn === 'sync://' || str_starts_with($dsn, 'sync://')) return 'sync';
        if (str_starts_with($dsn, 'in-memory://')) return 'in-memory';

        return 'unknown';
    }

    /** @return string[] */
    private function capabilities(string $type): array
    {
        return match ($type) {
            'doctrine' => ['count', 'list', 'failed', 'retry', 'delete', 'delayed'],
            'redis' => ['count', 'list', 'failed', 'retry', 'delete', 'delayed'],
            'amqp' => ['count', 'list', 'failed', 'retry', 'delete'],
            'sqs' => ['count', 'list', 'failed', 'retry', 'delete'],
            'sync' => ['list'],
            'in-memory' => ['count', 'list'],
            default => [],
        };
    }
}