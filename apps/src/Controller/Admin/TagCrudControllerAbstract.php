<?php

namespace Labstag\Controller\Admin;

use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use Labstag\Entity\Tag;
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

    public function configureFieldsDefault(): void
    {
        $this->crudFieldFactory->setTabPrincipal(self::getEntityFqcn());
        $this->crudFieldFactory->addFieldsToTab('principal', [$this->crudFieldFactory->titleField()]);
    }

    public static function getEntityFqcn(): string
    {
        return Tag::class;
    }
}
