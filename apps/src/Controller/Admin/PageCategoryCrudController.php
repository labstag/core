<?php

namespace Labstag\Controller\Admin;

use Override;

class PageCategoryCrudController extends CategoryCrudController
{
    #[Override]
    public function createEntity(string $entityFqcn)
    {
        $category = new $entityFqcn();
        $category->setType('page');

        return $category;
    }
}
