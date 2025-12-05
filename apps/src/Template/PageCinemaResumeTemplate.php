<?php

namespace Labstag\Template;

use Override;

class PageCinemaResumeTemplate extends TemplateAbstract
{
    #[Override]
    public function getCode(): string
    {
        return 'cinema_resume';
    }
}
