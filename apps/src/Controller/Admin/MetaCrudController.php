<?php

namespace Labstag\Controller\Admin;

use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use Labstag\Entity\Meta;
use Labstag\Field\MetaParentField;
use Labstag\Lib\AbstractCrudControllerLib;
use Symfony\Component\Translation\TranslatableMessage;

class MetaCrudController extends AbstractCrudControllerLib
{
    public function configureActions(Actions $actions): Actions
    {
        $this->configureActionsTrash($actions);
        $actions->remove(Crud::PAGE_INDEX, Action::NEW);

        return $actions;
    }

    public function configureFields(string $pageName): iterable
    {
        yield $this->addTabPrincipal();
        foreach ($this->crudFieldFactory->baseIdentitySet('meta', $pageName, self::getEntityFqcn(), withSlug: false, withImage: false, withEnable: false) as $field) { yield $field; }
        yield TextField::new('keywords', new TranslatableMessage('Keywords'));
        yield TextField::new('description', new TranslatableMessage('Description'));
        yield MetaParentField::new('parent', new TranslatableMessage('Parent'));
        foreach ($this->crudFieldFactory->dateSet() as $field) { yield $field; }
    }

    public static function getEntityFqcn(): string
    {
        return Meta::class;
    }
}
