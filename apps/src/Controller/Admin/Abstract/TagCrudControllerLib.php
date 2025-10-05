<?php

namespace Labstag\Controller\Admin\Abstract;

use Labstag\Entity\Tag;

abstract class TagCrudControllerLib extends AbstractTypedCrudControllerLib
{
    #[\Override]
    public function configureFields(string $pageName): iterable
    {
        // Tag is not uploadable: no image and no enable field in the entity
        return $this->crudFieldFactory->baseIdentitySet(
            'tag',
            $pageName,
            self::getEntityFqcn(),
            withSlug: true,
            withImage: false,
            withEnable: false
        );
    }

    public static function getEntityFqcn(): string
    {
        return Tag::class;
    }

    #[\Override]
    protected function getChildType(): string
    {
        return 'tag';
    }
}
