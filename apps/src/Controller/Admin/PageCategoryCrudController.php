<?php

namespace Labstag\Controller\Admin;

use Labstag\Entity\Category;
use Override;

class PageCategoryCrudController extends CategoryCrudController
{
    #[Override]
    public function createEntity(string $entityFqcn)
    {
        $category = new Category();
        $category->setType('page');

        return $category;
    }
}
