<?php

namespace Labstag\Controller\Admin;

use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use Symfony\Component\Translation\TranslatableMessage;

abstract class CategoryCrudControllerAbstract extends CrudControllerAbstract
{
    #[\Override]
    public function configureActions(Actions $actions): Actions
    {
        $this->actionsFactory->init($actions, $this->getEntityFqcn(), static::class);

        return $this->actionsFactory->show();
    }

    #[\Override]
    public function configureCrud(Crud $crud): Crud
    {
        $crud = parent::configureCrud($crud);
        $crud->setDefaultSort(
            ['title' => 'ASC']
        );
        $crud->setEntityLabelInSingular(new TranslatableMessage('Category'));
        $crud->setEntityLabelInPlural(new TranslatableMessage('Categories'));

        return $crud;
    }
}
