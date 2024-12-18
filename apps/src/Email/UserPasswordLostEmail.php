<?php

namespace Labstag\Email;

use Labstag\Lib\EmailLib;
use Override;

class UserPasswordLostEmail extends EmailLib
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
}
