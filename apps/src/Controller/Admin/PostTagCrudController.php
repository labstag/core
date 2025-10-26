<?php

namespace Labstag\Controller\Admin;

class PostTagCrudController extends TagCrudControllerAbstract
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
