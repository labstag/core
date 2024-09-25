<?php

namespace Labstag\Controller\Admin;

use Labstag\Entity\Category;
use Override;

class HistoryCategoryCrudController extends CategoryCrudController
{
    #[Override]
    public function createEntity(string $entityFqcn)
    {
        $category = new Category();
        $category->setType('history');

        return $category;
    }
}
