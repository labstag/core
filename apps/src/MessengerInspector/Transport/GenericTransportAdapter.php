<?php

declare(strict_types=1);

namespace Labstag\MessengerInspector\Transport;

class GenericTransportAdapter implements TransportAdapterInterface
{
    /**
     * @param string[] $capabilities
     */
    public function __construct(
        private readonly string $name,
        private readonly string $type,
        private readonly array $capabilities = [],
    )
    {
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function supports(string $capability): bool
    {
        return in_array($capability, $this->capabilities, true);
    }
}
