<?php

namespace Labstag\Email;

use Labstag\Lib\EmailLib;
use Override;

class UserSubmitEmail extends EmailLib
{
    #[Override]
    public function getName(): string
    {
        return 'New user';
    }

    #[Override]
    public function getType(): string
    {
        return 'user_submit';
    }
}
