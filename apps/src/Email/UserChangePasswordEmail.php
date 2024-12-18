<?php

namespace Labstag\Email;

use Labstag\Lib\EmailLib;
use Override;

class UserChangePasswordEmail extends EmailLib
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
}
