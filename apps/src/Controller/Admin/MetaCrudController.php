<?php

namespace Labstag\Controller\Admin;

use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use Labstag\Entity\Meta;
use Labstag\Field\MetaParentField;
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
        unset($pageName);
        yield $this->addFieldID();
        yield TextField::new('title');
        yield TextField::new('keywords');
        yield TextField::new('description');
        yield DateTimeField::new('createdAt')->hideOnForm();
        yield DateTimeField::new('updatedAt')->hideOnForm();
        yield MetaParentField::new('parent', 'Parent');
    }

    #[Override]
    public static function getEntityFqcn(): string
    {
        return Meta::class;
    }
}
