<?php

namespace Labstag\Controller\Admin;

use Labstag\Entity\Category;
use Override;

class PostCategoryCrudController extends CategoryCrudController
{
    #[Override]
    public function createEntity(string $entityFqcn)
    {
        $category = new Category();
        $category->setType('post');

        return $category;
    }
}
