<?php

namespace Labstag\Controller\Admin;

use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use Labstag\Entity\Category;
use Labstag\Lib\AbstractCrudControllerLib;

abstract class CategoryCrudController extends AbstractCrudControllerLib
{
    public function configureActions(Actions $actions): Actions
    {
        $this->configureActionsTrash($actions);

        return $actions;
    }

    public function configureCrud(Crud $crud): Crud
    {
        $crud = parent::configureCrud($crud);
        $crud->setDefaultSort(
            ['title' => 'ASC']
        );

        return $crud;
    }

    public function configureFields(string $pageName): iterable
    {
        // Category n'est pas uploadable : pas d'image, et pas de propriété enable dans l'entité -> on la retire
        return $this->crudFieldFactory->baseIdentitySet('category', $pageName, self::getEntityFqcn(), withSlug: true, withImage: false, withEnable: false);
    }

    public static function getEntityFqcn(): string
    {
        return Category::class;
    }
}
