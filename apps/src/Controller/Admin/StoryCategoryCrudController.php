<?php

namespace Labstag\Controller\Admin;

use Labstag\Controller\Admin\Abstract\CategoryCrudControllerLib;

class StoryCategoryCrudController extends CategoryCrudControllerLib
{
    protected function getChildRelationshipProperty(): string
    {
        return 'stories';
    }

    protected function getEntityType(): string
    {
        return 'story';
    }
}
