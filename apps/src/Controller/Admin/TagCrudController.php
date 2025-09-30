<?php

namespace Labstag\Controller\Admin;

use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use Labstag\Entity\Tag;
use Labstag\Lib\AbstractCrudControllerLib;

abstract class TagCrudController extends AbstractCrudControllerLib
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
        // Tag n'est pas uploadable : pas d'image et pas de champ enable dans l'entité
        return $this->crudFieldFactory->baseIdentitySet('tag', $pageName, self::getEntityFqcn(), withSlug: true, withImage: false, withEnable: false);
    }

    public static function getEntityFqcn(): string
    {
        return Tag::class;
    }
}
