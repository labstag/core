<?php

namespace Labstag\Email;

use Labstag\Lib\EmailLib;
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
}
