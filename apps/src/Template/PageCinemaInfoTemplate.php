<?php

namespace Labstag\Template;

use Override;

class PageCinemaInfoTemplate extends TemplateAbstract
{
    #[Override]
    public function getCode(): string
    {
        return 'cinema_info';
    }
}
