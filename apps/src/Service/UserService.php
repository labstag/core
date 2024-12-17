<?php

namespace Labstag\Service;

use EasyCorp\Bundle\EasyAdminBundle\Config\Locale;
use Labstag\Entity\User;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserService
{
    public function __construct(
        protected UserPasswordHasherInterface $userPasswordHasher
    )
    {
    }

    public function getLanguages(): array
    {
        return [
            'fr',
            'en',
        ];
    }

    public function getLanguagesForChoices(): array
    {
        $data      = $this->getLanguages();
        $languages = [];
        foreach ($data as $key) {
            $languages[$key] = Locale::new($key)->getAsDto()->getName();
        }

        return array_flip($languages);
    }

    public function getRoles(): array
    {
        return [
            'User'        => 'ROLE_USER',
            'Admin'       => 'ROLE_ADMIN',
            'Super Admin' => 'ROLE_SUPER_ADMIN',
        ];
    }

    public function getTemplates(): array
    {
        return [
            'user_check'                  => 'Confirmation création compte',
            'user_check_oauthconnectuser' => 'Nouvelle association',
            'user_check_mail'             => 'Ajout nouveau courriel',
            'user_change-email-principal' => 'Changement de courriel principal',
            'user_password-lost'          => 'Demande de nouveau mot de passe',
            'user_password-change'        => 'Mot de passe changé',
        ];
    }

    public function hashPassword(User $user, string $password): string
    {
        return $this->userPasswordHasher->hashPassword($user, $password);
    }
}
