<?php

namespace Labstag\Controller\Admin\Abstract;

use Labstag\Entity\Category;

abstract class CategoryCrudControllerLib extends AbstractTypedCrudControllerLib
{
    #[\Override]
    protected function getChildType(): string
    {
        return 'category';
    }

    #[\Override]
    public function configureFields(string $pageName): iterable
    {
        // Category is not uploadable: no image, and no enable property in the entity -> remove it
        return $this->crudFieldFactory->baseIdentitySet(
            'category',
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
}
