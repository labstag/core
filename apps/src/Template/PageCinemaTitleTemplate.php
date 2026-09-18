<?php

namespace Labstag\Template;

use Override;

class PageCinemaTitleTemplate extends TemplateAbstract
{
    #[Override]
    public function getCode(): string
    {
        return 'cinema_title';
    }
}
