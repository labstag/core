<?php

namespace Labstag\Service;

use EasyCorp\Bundle\EasyAdminBundle\Config\Locale;
use Labstag\Entity\User;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

final class UserService
{
    public function __construct(
        private UserPasswordHasherInterface $userPasswordHasher,
    )
    {
    }

    /**
     * @return mixed[]
     */
    public function getLanguages(): array
    {
        return [
            'fr',
            'en',
        ];
    }

    /**
     * @return mixed[]
     */
    public function getLanguagesForChoices(): array
    {
        $data      = $this->getLanguages();
        $languages = [];
        foreach ($data as $key) {
            $languages[$key] = Locale::new($key)->getAsDto()->getName();
        }

        return array_flip($languages);
    }

    /**
     * @return mixed[]
     */
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
