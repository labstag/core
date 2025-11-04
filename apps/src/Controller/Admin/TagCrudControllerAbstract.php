<?php

namespace Labstag\Controller\Admin;

use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use Symfony\Component\Translation\TranslatableMessage;

abstract class TagCrudControllerAbstract extends TypedCrudControllerAbstract
{
    #[\Override]
    public function configureCrud(Crud $crud): Crud
    {
        $crud = parent::configureCrud($crud);
        $crud->setEntityLabelInSingular(new TranslatableMessage('Tag'));
        $crud->setEntityLabelInPlural(new TranslatableMessage('Tags'));

        return $crud;
    }
}
