<?php

namespace Labstag\Template;

use Override;

class PageMovieInfoTemplate extends TemplateAbstract
{
    #[Override]
    public function getCode(): string
    {
        return 'movie_info';
    }
}
