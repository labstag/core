<?php

namespace Labstag\Controller\Admin;

use Labstag\Lib\CategoryCrudControllerLib;

class PostCategoryCrudController extends CategoryCrudControllerLib
{
    #[\Override]
    public function configureFields(string $pageName): iterable
    {
        return $this->configureBaseFields($pageName);
    }

    protected function getChildRelationshipProperty(): string
    {
        return 'posts';
    }

    protected function getEntityType(): string
    {
        return 'post';
    }
}
