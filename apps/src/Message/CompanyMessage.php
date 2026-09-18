<?php

namespace Labstag\Message;

use Symfony\Component\Messenger\Attribute\AsMessage;

#[AsMessage('async')]
final class CompanyMessage
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
