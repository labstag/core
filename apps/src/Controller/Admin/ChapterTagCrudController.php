<?php

namespace Labstag\Controller\Admin;

class ChapterTagCrudController extends TagCrudControllerAbstract
{
    protected function getChildRelationshipProperty(): string
    {
        return 'chapters';
    }

    protected function getEntityType(): string
    {
        return 'chapter';
    }
}
