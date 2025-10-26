<?php

namespace Labstag\Controller\Admin;

class PageCategoryCrudController extends CategoryCrudControllerAbstract
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
