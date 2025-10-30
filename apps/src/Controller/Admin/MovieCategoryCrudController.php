<?php

namespace Labstag\Controller\Admin;

use EasyCorp\Bundle\EasyAdminBundle\Field\CollectionField;
use Symfony\Component\Translation\TranslatableMessage;

class MovieCategoryCrudController extends CategoryCrudControllerAbstract
{
    #[\Override]
    public function configureFields(string $pageName): iterable
    {
        unset($pageName);
        $this->configureFieldsDefault();
        $collectionField = CollectionField::new('movies', new TranslatableMessage('Movies'));
        $collectionField->formatValue(fn ($entity): int => count($entity));
        $collectionField->hideOnForm();

        $this->crudFieldFactory->addFieldsToTab('principal', [$collectionField]);
        yield from $this->crudFieldFactory->getConfigureFields();
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
