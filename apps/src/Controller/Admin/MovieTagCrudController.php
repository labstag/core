<?php

namespace Labstag\Controller\Admin;

use Labstag\Controller\Admin\Abstract\TagCrudControllerLib;

class MovieTagCrudController extends TagCrudControllerLib
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
