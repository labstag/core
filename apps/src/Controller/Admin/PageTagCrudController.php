<?php

namespace Labstag\Controller\Admin;

use Labstag\Controller\Admin\Abstract\TagCrudControllerLib;

class PageTagCrudController extends TagCrudControllerLib
{
    protected function getChildRelationshipProperty(): string
    {
        return 'pages';
    }

    protected function getEntityType(): string
    {
        return 'page';
    }
}
