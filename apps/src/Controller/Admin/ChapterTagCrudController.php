<?php

namespace Labstag\Controller\Admin;

use Override;

class ChapterTagCrudController extends TagCrudController
{
    #[Override]
    public function createEntity(string $entityFqcn)
    {
        $tag = new $entityFqcn();
        $tag->setType('chapter');

        return $tag;
    }
}
