<?php

namespace Labstag\MessengerInspector\Transport;

interface TransportAdapterInterface
{
    public function getName(): string;
    public function getType(): string;
    public function supports(string $capability): bool;
}