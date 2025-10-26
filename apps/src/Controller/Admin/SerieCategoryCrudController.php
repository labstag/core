<?php

namespace Labstag\Controller\Admin;

class SerieCategoryCrudController extends CategoryCrudControllerAbstract
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
