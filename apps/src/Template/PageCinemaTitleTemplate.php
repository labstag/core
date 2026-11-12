<?php

declare(strict_types=1);

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
