<?php

namespace Labstag\Controller\Admin;

class PostCategoryCrudController extends CategoryCrudControllerAbstract
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
