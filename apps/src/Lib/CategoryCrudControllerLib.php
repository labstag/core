<?php

namespace Labstag\Lib;

use Labstag\Entity\Category;

abstract class CategoryCrudControllerLib extends AbstractTypedCrudControllerLib
{
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
