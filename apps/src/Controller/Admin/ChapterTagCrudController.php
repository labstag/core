<?php

namespace Labstag\Controller\Admin;

use Labstag\Lib\TagCrudControllerLib;

class ChapterTagCrudController extends TagCrudControllerLib
{
    #[\Override]
    public function configureFields(string $pageName): iterable
    {
        return $this->configureBaseFields($pageName);
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
