<?php

namespace Labstag\Controller\Admin;

use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use Labstag\Entity\Category;
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

    public function configureFieldsDefault(): void
    {
        $this->crudFieldFactory->setTabPrincipal(self::getEntityFqcn());
        $this->crudFieldFactory->addFieldsToTab(
            'principal',
            [
                $this->crudFieldFactory->slugField(),
                $this->crudFieldFactory->titleField(),
            ]
        );
    }

    public static function getEntityFqcn(): string
    {
        return Category::class;
    }

    #[\Override]
    protected function getChildType(): string
    {
        return 'category';
    }
}
