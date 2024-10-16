<?php

namespace Labstag\Controller\Admin;

use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use Labstag\Entity\Category;
use Labstag\Lib\AbstractCrudControllerLib;
use Override;

abstract class CategoryCrudController extends AbstractCrudControllerLib
{
    #[Override]
    public function configureActions(Actions $actions): Actions
    {
        $this->configureActionsTrash($actions);

        return $actions;
    }

    #[Override]
    public function configureFields(string $pageName): iterable
    {
        unset($pageName);

        return [
            $this->addFieldID(),
            $this->addFieldSlug(),
            TextField::new('title'),
        ];
    }

    #[Override]
    public static function getEntityFqcn(): string
    {
        return Category::class;
    }
}
