<?php

namespace Labstag\Replace;

use Labstag\Replace\Abstract\ReplaceLib;

class UserEmailReplace extends ReplaceLib
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
