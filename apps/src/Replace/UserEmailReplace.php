<?php

namespace Labstag\Replace;

class UserEmailReplace extends ReplaceAbstract
{
    public function exec(): string
    {
        if (!isset($this->data['user'])) {
            return '';
        }

        return $this->data['user']->getEmail();
    }

    public function getCode(): string
    {
        return 'user_email';
    }

    public function getTitle(): string
    {
        return 'User email';
    }
}
