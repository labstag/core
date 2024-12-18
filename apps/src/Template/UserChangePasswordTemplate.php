<?php

namespace Labstag\Template;

use Labstag\Lib\TemplateLib;
use Override;

class UserChangePasswordTemplate extends TemplateLib
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
