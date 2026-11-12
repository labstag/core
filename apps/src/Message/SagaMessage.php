<?php

declare(strict_types=1);

namespace Labstag\Message;

use Symfony\Component\Messenger\Attribute\AsMessage;

#[AsMessage('async')]
final class SagaMessage
{
    public function __construct(
        private string $data,
    )
    {
    }

    public function getData(): string
    {
        return $this->data;
    }
}
