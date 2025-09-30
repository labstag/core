<?php

namespace Labstag\Controller\Admin;

use Labstag\Lib\TagCrudControllerLib;

class MovieTagCrudController extends TagCrudControllerLib
{
    #[\Override]
    public function configureFields(string $pageName): iterable
    {
        return $this->configureBaseFields($pageName);
    }

    protected function getChildRelationshipProperty(): string
    {
        return 'movies';
    }

    protected function getEntityType(): string
    {
        return 'movie';
    }
}
