<?php

namespace Labstag\Lib\Admin\Traits;

use EasyCorp\Bundle\EasyAdminBundle\Field\CollectionField;
use Symfony\Component\Translation\TranslatableMessage;

/**
 * Trait for user-related collection fields to reduce duplication across UserCrudController and ProfilCrudController.
 */
trait UserCollectionFieldsTrait
{
    protected function configureUserCollectionFields(): iterable
    {
        yield CollectionField::new('stories', new TranslatableMessage('Histories'))->onlyOnDetail();
        yield CollectionField::new('editos', new TranslatableMessage('Editos'))->onlyOnDetail()->formatValue(
            fn ($entity): int => count($entity)
        );

        $collectionTypes = [
            'editos' => new TranslatableMessage('Editos'),
            'memos'  => new TranslatableMessage('Memos'),
            'pages'  => new TranslatableMessage('Pages'),
            'posts'  => new TranslatableMessage('Posts'),
        ];

        foreach ($collectionTypes as $propertyName => $label) {
            $collectionField = CollectionField::new($propertyName, $label);
            $collectionField->onlyOnDetail();
            $collectionField->formatValue(fn ($value): int => count($value));
            yield $collectionField;
        }
    }
}
