<?php

namespace Labstag\Controller\Admin;

use Labstag\Entity\Tag;
use Override;

class ChapterTagCrudController extends TagCrudController
{
    #[Override]
    public function createEntity(string $entityFqcn)
    {
        $tag = new Tag();
        $tag->setType('chapter');

        return $tag;
    }
}
