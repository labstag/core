<?php

namespace Labstag\Template;

use Labstag\Lib\TemplateLib;
use Override;

class UserApprovalTemplate extends TemplateLib
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
