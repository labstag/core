<?php

namespace Labstag\Replace;

use Labstag\Replace\Abstract\ReplaceLib;

class UserRolesReplace extends ReplaceLib
{
    public function exec(): string
    {
        if (!isset($this->data['user'])) {
            return '';
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
