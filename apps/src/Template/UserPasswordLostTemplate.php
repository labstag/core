<?php

namespace Labstag\Template;

use Labstag\Lib\TemplateLib;
use Override;

class UserPasswordLostTemplate extends TemplateLib
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
