<?php

namespace Labstag\Controller\Admin;

use Labstag\Entity\Tag;
use Override;

class MemoTagCrudController extends TagCrudController
{
    #[Override]
    public function createEntity(string $entityFqcn)
    {
        $tag = new Tag();
        $tag->setType('memo');

        return $tag;
    }
}
