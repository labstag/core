<?php

namespace Labstag\Controller\Admin;

use Labstag\Controller\Admin\Abstract\TagCrudControllerLib;

class StoryTagCrudController extends TagCrudControllerLib
{
    #[\Override]
    public function configureFields(string $pageName): iterable
    {
        return $this->configureBaseFields();
    }

    protected function getChildRelationshipProperty(): string
    {
        return 'stories';
    }

    protected function getEntityType(): string
    {
        return 'story';
    }
}
