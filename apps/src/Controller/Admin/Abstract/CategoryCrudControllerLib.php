<?php

namespace Labstag\Controller\Admin\Abstract;

use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use Labstag\Entity\Category;
use Symfony\Component\Translation\TranslatableMessage;

abstract class CategoryCrudControllerLib extends AbstractTypedCrudControllerLib
{
    #[\Override]
    public function configureCrud(Crud $crud): Crud
    {
        $crud = parent::configureCrud($crud);
        $crud->setEntityLabelInSingular(new TranslatableMessage('Category'));
        $crud->setEntityLabelInPlural(new TranslatableMessage('Categories'));

        return $crud;
    }

    #[\Override]
    public function configureFields(string $pageName): iterable
    {
        // Category is not uploadable: no image, and no enable property in the entity -> remove it
        return $this->crudFieldFactory->baseIdentitySet(
            $pageName,
            self::getEntityFqcn(),
            withSlug: true,
            withImage: false,
            withEnable: false
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
