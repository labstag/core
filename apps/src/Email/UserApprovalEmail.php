<?php

namespace Labstag\Email;

use Labstag\Lib\EmailLib;
use Override;

class UserApprovalEmail extends EmailLib
{
    #[Override]
    public function getName(): string
    {
        return 'User approval';
    }

    #[Override]
    public function getType(): string
    {
        return 'user_approval';
    }
}
