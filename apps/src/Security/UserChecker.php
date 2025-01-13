<?php

namespace Labstag\Security;

use Labstag\Entity\User;
use Symfony\Component\Security\Core\Exception\AccountExpiredException;
use Symfony\Component\Security\Core\User\UserCheckerInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Override;

class UserChecker implements UserCheckerInterface
{
    #[Override]
    public function checkPostAuth(UserInterface $user): void
    {
        if (!$user instanceof User) {
            return;
        }

        if (!$user->isEnable()) {
            throw new AccountExpiredException('Your account is disabled.');
        }
    }

    #[Override]
    public function checkPreAuth(UserInterface $user): void
    {
        unset($user);
    }
}
