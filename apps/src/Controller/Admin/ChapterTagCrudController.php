<?php

namespace Labstag\Controller\Admin;

use Labstag\Controller\Admin\Abstract\TagCrudControllerLib;

class ChapterTagCrudController extends TagCrudControllerLib
{
    #[\Override]
    public function configureFields(string $pageName): iterable
    {
        return $this->configureBaseFields();
    }

    protected function getChildRelationshipProperty(): string
    {
        return 'chapters';
    }

    protected function getEntityType(): string
    {
        return 'chapter';
    }
}
