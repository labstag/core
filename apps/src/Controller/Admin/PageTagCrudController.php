<?php

namespace Labstag\Controller\Admin;

class PageTagCrudController extends TagCrudControllerAbstract
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
