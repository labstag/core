<?php

namespace Labstag\Email;

use Labstag\Lib\EmailLib;
use Override;

class UserActivateEmail extends EmailLib
{
    #[Override]
    public function getName(): string
    {
        return 'User activate';
    }

    #[Override]
    public function getType(): string
    {
        return 'user_activate';
    }
}
