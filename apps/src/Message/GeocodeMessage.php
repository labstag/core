<?php

namespace Labstag\Message;

use Symfony\Component\Messenger\Attribute\AsMessage;

#[AsMessage('async')]
final class GeocodeMessage
{
    public function __construct(
        private array $data,
    )
    {
    }

    public function getData(): array
    {
        return $this->data;
    }
}
