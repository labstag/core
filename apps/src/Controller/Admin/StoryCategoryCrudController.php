<?php

namespace Labstag\Controller\Admin;

use EasyCorp\Bundle\EasyAdminBundle\Field\CollectionField;
use Symfony\Component\Translation\TranslatableMessage;

class StoryCategoryCrudController extends CategoryCrudControllerAbstract
{
    #[\Override]
    public function configureFields(string $pageName): iterable
    {
        yield from parent::configureFields($pageName);
        yield CollectionField::new('stories', new TranslatableMessage('Stories'))->formatValue(
            fn ($entity): int => count($entity)
        )->hideOnForm();
    }

    protected function getChildRelationshipProperty(): string
    {
        return 'stories';
    }

    protected function getEntityType(): string
    {
        return 'story';
    }
}
