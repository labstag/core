<?php

namespace Labstag\Controller\Admin;

use Override;
use Labstag\Entity\Category;

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
