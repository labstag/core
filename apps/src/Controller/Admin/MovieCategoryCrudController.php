<?php

namespace Labstag\Controller\Admin;

use Labstag\Controller\Admin\Abstract\CategoryCrudControllerLib;

class MovieCategoryCrudController extends CategoryCrudControllerLib
{
    protected function getChildRelationshipProperty(): string
    {
        return 'movies';
    }

    protected function getEntityType(): string
    {
        return 'movie';
    }
}
