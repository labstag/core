<?php

namespace Labstag\MessengerInspector\Transport;

use Symfony\Component\Messenger\Transport\TransportInterface;

class TransportRegistry
{
    /** @var TransportAdapterInterface[] */
    private array $adapters = [];

    public function register(TransportAdapterInterface $adapter): void
    {
        $this->adapters[] = $adapter;
    }

    /** @return TransportAdapterInterface[] */
    public function all(): array
    {
        return $this->adapters;
    }
}