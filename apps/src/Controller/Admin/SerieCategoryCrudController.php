<?php

namespace Labstag\Controller\Admin;

use Labstag\Controller\Admin\Abstract\CategoryCrudControllerLib;

class SerieCategoryCrudController extends CategoryCrudControllerLib
{
    protected function getChildRelationshipProperty(): string
    {
        return 'stories';
    }

    protected function getEntityType(): string
    {
        return 'serie';
    }
}
