<?php

namespace Labstag\Controller\Admin;

use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use Symfony\Component\Translation\TranslatableMessage;

abstract class CategoryCrudControllerAbstract extends TypedCrudControllerAbstract
{
    #[\Override]
    public function configureCrud(Crud $crud): Crud
    {
        $crud = parent::configureCrud($crud);
        $crud->setEntityLabelInSingular(new TranslatableMessage('Category'));
        $crud->setEntityLabelInPlural(new TranslatableMessage('Categories'));

        return $crud;
    }
}
