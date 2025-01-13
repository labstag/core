<?php

namespace Labstag\Replace;

use Labstag\Lib\ReplaceLib;

class UserRolesReplace extends ReplaceLib
{
    public function exec(): string
    {
        if (!isset($this->data['user'])) {
            return null;
        }

        $roles = $this->data['user']->getRoles();

        return implode(', ', $roles);
    }

    public function getCode(): string
    {
        return 'user_roles';
    }

    public function getTitle(): string
    {
        return 'User roles';
    }
}
