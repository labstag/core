<?php

namespace Labstag\Controller\Admin;

use EasyCorp\Bundle\EasyAdminBundle\Field\CollectionField;
use Symfony\Component\Translation\TranslatableMessage;

class MovieCategoryCrudController extends CategoryCrudControllerAbstract
{
    #[\Override]
    public function configureFields(string $pageName): iterable
    {
        yield from parent::configureFields($pageName);
        yield CollectionField::new('movies', new TranslatableMessage('Movies'))->formatValue(
            fn ($entity): int => count($entity)
        )->hideOnForm();
    }

    protected function getChildRelationshipProperty(): string
    {
        return 'movies';
    }

    protected function getEntityType(): string
    {
        return 'movie';
    }
}
