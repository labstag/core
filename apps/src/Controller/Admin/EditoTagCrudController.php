<?php

namespace Labstag\Controller\Admin;

use Override;
use Labstag\Entity\Tag;

class EditoTagCrudController extends TagCrudController
{
    #[Override]
    public function createEntity(string $entityFqcn)
    {
        $tag = new Tag();
        $tag->setType('edito');

        return $tag;
    }
}
