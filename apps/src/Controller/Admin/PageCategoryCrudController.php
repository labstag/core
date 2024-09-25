<?php

namespace Labstag\Controller\Admin;

use Override;
use Labstag\Entity\Category;

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
