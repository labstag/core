<?php

namespace Labstag\Controller\Admin;

use Override;

class PostCategoryCrudController extends CategoryCrudController
{
    #[Override]
    public function createEntity(string $entityFqcn)
    {
        $category = new $entityFqcn();
        $category->setType('post');

        return $category;
    }
}
