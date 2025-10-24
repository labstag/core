<?php

namespace Labstag\Email;

use Labstag\Email\Abstract\EmailLib;
use Override;

class UserDeactivateEmail extends EmailLib
{
    #[Override]
    public function getName(): string
    {
        return 'User deactivate';
    }

    #[Override]
    public function getType(): string
    {
        return 'user_deactivate';
    }

    #[Override]
    public function init(): void
    {
        $user = $this->data['user'];
        parent::init();
        $this->to($user->getEmail());
    }
}
