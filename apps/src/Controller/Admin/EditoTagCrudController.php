<?php

namespace Labstag\Controller\Admin;

use Override;

class EditoTagCrudController extends TagCrudController
{
    #[Override]
    public function createEntity(string $entityFqcn)
    {
        $tag = new $entityFqcn();
        $tag->setType('edito');

        return $tag;
    }
}
