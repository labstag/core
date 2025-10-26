<?php

namespace Labstag\Controller\Admin;

use EasyCorp\Bundle\EasyAdminBundle\Field\CollectionField;
use Symfony\Component\Translation\TranslatableMessage;

class ChapterTagCrudController extends TagCrudControllerAbstract
{
    #[\Override]
    public function configureFields(string $pageName): iterable
    {
        yield from parent::configureFields($pageName);
        yield CollectionField::new('chapters', new TranslatableMessage('Chapters'))->formatValue(
            fn ($entity): int => count($entity)
        )->hideOnForm();
    }

    protected function getChildRelationshipProperty(): string
    {
        return 'chapters';
    }

    protected function getEntityType(): string
    {
        return 'chapter';
    }
}
