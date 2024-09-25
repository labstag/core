<?php

namespace Labstag\Controller\Admin;

use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use Labstag\Entity\Meta;
use Labstag\Lib\AbstractCrudControllerLib;
use Override;

class MetaCrudController extends AbstractCrudControllerLib
{
    #[Override]
    public function configureActions(Actions $actions): Actions
    {
        $this->configureActionsTrash($actions);
        $actions->remove(Crud::PAGE_INDEX, Action::NEW);

        return $actions;
    }

    #[Override]
    public function configureFields(string $pageName): iterable
    {
        yield $this->addFieldID();
        yield TextField::new('title');
        yield TextField::new('keywords');
        yield TextField::new('description');
        yield DateTimeField::new('createdAt')->hideOnForm();
        yield DateTimeField::new('updatedAt')->hideOnForm();
        // TODO : Prévoir de mettre qu'un champs à la place de tous les champs
        yield AssociationField::new('chapter')->hideOnForm();
        yield AssociationField::new('edito')->hideOnForm();
        yield AssociationField::new('history')->hideOnForm();
        yield AssociationField::new('page')->hideOnForm();
        yield AssociationField::new('post')->hideOnForm();
    }

    #[Override]
    public static function getEntityFqcn(): string
    {
        return Meta::class;
    }
}
