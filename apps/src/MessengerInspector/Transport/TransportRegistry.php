<?php

declare(strict_types=1);

namespace Labstag\MessengerInspector\Transport;

class TransportRegistry
{

    /**
     * @var TransportAdapterInterface[]
     */
    private array $adapters = [];

    /**
     * @return TransportAdapterInterface[]
     */
    public function all(): array
    {
        return $this->adapters;
    }

    public function register(TransportAdapterInterface $adapter): void
    {
        $this->adapters[] = $adapter;
    }
}
