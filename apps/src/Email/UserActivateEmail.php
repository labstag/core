<?php

namespace Labstag\Email;

use Labstag\Email\Abstract\EmailLib;
use Override;

class UserActivateEmail extends EmailLib
{
    #[Override]
    public function getName(): string
    {
        return 'User activate %user_username%';
    }

    #[Override]
    public function getType(): string
    {
        return 'user_activate';
    }

    #[Override]
    public function init(): void
    {
        $user = $this->data['user'];
        parent::init();
        $this->to($user->getEmail());
    }
}
