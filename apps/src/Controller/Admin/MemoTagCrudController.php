<?php

namespace Labstag\Controller\Admin;

use Override;

class MemoTagCrudController extends TagCrudController
{
    #[Override]
    public function createEntity(string $entityFqcn)
    {
        $tag = new $entityFqcn();
        $tag->setType('memo');

        return $tag;
    }
}
