<?php

namespace Labstag\Controller\Admin;

use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use Labstag\Entity\Tag;
use Override;

class PageTagCrudController extends TagCrudController
{
    #[Override]
    public function configureActions(Actions $actions): Actions
    {
        $this->configureActionsTrash($actions);

        return $actions;
    }

    #[Override]
    public static function getEntityFqcn(): string
    {
        return Tag::class;
    }
}
