<?php

namespace Labstag\Email;

use Override;

class UserPasswordLostEmail extends EmailAbstract
{
    #[Override]
    public function getName(): string
    {
        return 'Password Losted';
    }

    #[Override]
    public function getType(): string
    {
        return 'user_passwordlost';
    }

    #[Override]
    public function init(): void
    {
        $user = $this->data['user'];
        parent::init();
        $this->to($user->getEmail());
    }
}
