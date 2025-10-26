<?php

namespace Labstag\Controller\Admin;

use EasyCorp\Bundle\EasyAdminBundle\Field\CollectionField;
use Symfony\Component\Translation\TranslatableMessage;

class PageTagCrudController extends TagCrudControllerAbstract
{
    #[\Override]
    public function configureFields(string $pageName): iterable
    {
        yield from parent::configureFields($pageName);
        yield CollectionField::new('pages', new TranslatableMessage('Pages'))->formatValue(
            fn ($entity): int => count($entity)
        )->hideOnForm();
    }

    protected function getChildRelationshipProperty(): string
    {
        return 'pages';
    }

    protected function getEntityType(): string
    {
        return 'page';
    }
}
