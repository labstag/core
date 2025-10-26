<?php

namespace Labstag\Controller\Admin;

class StoryTagCrudController extends TagCrudControllerAbstract
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
