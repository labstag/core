<?php

namespace Labstag\Template;

use Labstag\Lib\TemplateLib;
use Override;

class UserDeactivateTemtplate extends TemplateLib
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
