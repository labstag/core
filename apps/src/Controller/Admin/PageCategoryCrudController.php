<?php

namespace Labstag\Controller\Admin;

use Labstag\Controller\Admin\Abstract\CategoryCrudControllerLib;

class PageCategoryCrudController extends CategoryCrudControllerLib
{
    #[\Override]
    public function configureFields(string $pageName): iterable
    {
        return $this->configureBaseFields();
    }

    protected function getChildRelationshipProperty(): string
    {
        return 'pages';
    }

    protected function getEntityType(): string
    {
        return 'page';
    }
}
