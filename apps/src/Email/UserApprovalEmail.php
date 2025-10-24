<?php

namespace Labstag\Email;

use Labstag\Email\Abstract\EmailLib;
use Override;

class UserApprovalEmail extends EmailLib
{
    #[Override]
    public function getName(): string
    {
        return 'User approval %user_username%';
    }

    #[Override]
    public function getType(): string
    {
        return 'user_approval';
    }

    #[Override]
    public function init(): void
    {
        $user = $this->data['user'];
        parent::init();
        $this->to($user->getEmail());
    }
}
