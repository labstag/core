<?php

namespace Labstag\Controller\Admin;

use Labstag\Controller\Admin\Abstract\TagCrudControllerLib;

class StoryTagCrudController extends TagCrudControllerLib
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
