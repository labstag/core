<?php

namespace Labstag\Template;

use Labstag\Lib\TemplateLib;
use Override;

class UserSubmitTemplate extends TemplateLib
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
