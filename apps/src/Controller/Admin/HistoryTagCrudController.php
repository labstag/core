<?php

namespace Labstag\Controller\Admin;

use Override;
use Labstag\Entity\Tag;

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
