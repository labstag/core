<?php

namespace Labstag\Controller\Admin;

class StoryCategoryCrudController extends CategoryCrudControllerAbstract
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
