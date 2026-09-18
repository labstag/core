<?php

namespace Labstag\Message;

use Symfony\Component\Messenger\Attribute\AsMessage;

#[AsMessage('async')]
final class UserEmailMessage
{
    public function __construct(
        private string $username,
        private string $template,
    )
    {
    }

    public function getTemplate(): string
    {
        return $this->template;
    }

    public function getUsername(): string
    {
        return $this->username;
    }
}
