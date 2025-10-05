<?php

namespace Labstag\Controller\Admin;

use Labstag\Controller\Admin\Abstract\TagCrudControllerLib;

class MovieTagCrudController extends TagCrudControllerLib
{
    #[\Override]
    public function configureFields(string $pageName): iterable
    {
        return $this->configureBaseFields();
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
