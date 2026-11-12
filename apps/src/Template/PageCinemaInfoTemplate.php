<?php

declare(strict_types=1);

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
