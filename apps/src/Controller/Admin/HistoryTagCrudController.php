<?php

namespace Labstag\Controller\Admin;

use Override;

class HistoryTagCrudController extends TagCrudController
{
    #[Override]
    public function createEntity(string $entityFqcn)
    {
        $tag = new $entityFqcn();
        $tag->setType('history');

        return $tag;
    }
}
