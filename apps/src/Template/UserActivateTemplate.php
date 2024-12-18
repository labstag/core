<?php

namespace Labstag\Template;

use Labstag\Lib\TemplateLib;
use Override;

class UserActivateTemplate extends TemplateLib
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
