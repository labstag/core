<?php

namespace Labstag\Service;

use Labstag\Entity\User;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserService
{
    public function __construct(
        protected UserPasswordHasherInterface $userPasswordHasher
    )
    {
    }

    public function getRoles(): array
    {
        return [
            'User'        => 'ROLE_USER',
            'Admin'       => 'ROLE_ADMIN',
            'Super Admin' => 'ROLE_SUPER_ADMIN',
        ];
    }

    public function hashPassword(User $user, string $password): string
    {
        return $this->userPasswordHasher->hashPassword($user, $password);
    }
}
