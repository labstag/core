<?php

namespace Labstag\Message;

use Symfony\Component\Messenger\Attribute\AsMessage;

#[AsMessage('async')]
final class SerieMessage
{
    public function __construct(
        private string $serie,
    )
    {
    }

    public function getSerie(): string
    {
        return $this->serie;
    }
}
