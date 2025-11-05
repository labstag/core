<?php

namespace Labstag\Email;

use Override;

class UserChangePasswordEmail extends EmailAbstract
{
    #[Override]
    public function getName(): string
    {
        return 'Change password';
    }

    #[Override]
    public function getType(): string
    {
        return 'user_changepassword';
    }

    #[Override]
    public function init(): void
    {
        $user = $this->data['user'];
        parent::init();
        $this->to($user->getEmail());
    }
}
