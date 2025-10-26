<?php

namespace Labstag\Controller\Admin;

class MovieCategoryCrudController extends CategoryCrudControllerAbstract
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
