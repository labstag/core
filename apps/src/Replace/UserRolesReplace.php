<?php

namespace Labstag\Replace;

class UserRolesReplace extends ReplaceAbstract
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
