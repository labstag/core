<?php

namespace Labstag\Controller\Admin;

use Labstag\Controller\Admin\Abstract\TagCrudControllerLib;

class PostTagCrudController extends TagCrudControllerLib
{
    protected function getChildRelationshipProperty(): string
    {
        return 'posts';
    }

    protected function getEntityType(): string
    {
        return 'post';
    }
}
