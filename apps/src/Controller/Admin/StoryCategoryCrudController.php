<?php

namespace Labstag\Controller\Admin;

use Labstag\Controller\Admin\Abstract\CategoryCrudControllerLib;

class StoryCategoryCrudController extends CategoryCrudControllerLib
{
    #[\Override]
    public function configureFields(string $pageName): iterable
    {
        return $this->configureBaseFields($pageName);
    }

    protected function getChildRelationshipProperty(): string
    {
        return 'stories';
    }

    protected function getEntityType(): string
    {
        return 'story';
    }
}
