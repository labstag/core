<?php

namespace Labstag\Controller\Admin;

use Override;

class HistoryCategoryCrudController extends CategoryCrudController
{
    #[Override]
    public function createEntity(string $entityFqcn)
    {
        $category = new $entityFqcn();
        $category->setType('history');

        return $category;
    }
}
