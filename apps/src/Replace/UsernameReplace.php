<?php

namespace Labstag\Replace;

use Labstag\Lib\ReplaceLib;

class UsernameReplace extends ReplaceLib
{
    public function exec(): string
    {
        if (!isset($this->data['user'])) {
            return '';
        }

        return $this->data['user']->getUsername();
    }

    public function getCode(): string
    {
        return 'user_username';
    }

    public function getTitle(): string
    {
        return 'Username';
    }
}
