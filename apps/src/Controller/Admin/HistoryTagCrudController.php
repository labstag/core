<?php

namespace Labstag\Controller\Admin;

use Labstag\Entity\Tag;
use Override;

class HistoryTagCrudController extends TagCrudController
{
    #[Override]
    public function createEntity(string $entityFqcn)
    {
        $tag = new Tag();
        $tag->setType('history');

        return $tag;
    }
}
