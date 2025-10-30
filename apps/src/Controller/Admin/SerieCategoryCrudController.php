<?php

namespace Labstag\Controller\Admin;

use EasyCorp\Bundle\EasyAdminBundle\Field\CollectionField;
use Symfony\Component\Translation\TranslatableMessage;

class SerieCategoryCrudController extends CategoryCrudControllerAbstract
{
    #[\Override]
    public function configureFields(string $pageName): iterable
    {
        unset($pageName);
        $this->configureFieldsDefault();
        $collectionField = CollectionField::new('series', new TranslatableMessage('Series'));
        $collectionField->formatValue(fn ($entity): int => count($entity));
        $collectionField->hideOnForm();

        $this->crudFieldFactory->addFieldsToTab('principal', [$collectionField]);

        yield from $this->crudFieldFactory->getConfigureFields();
    }

    protected function getChildRelationshipProperty(): string
    {
        return 'stories';
    }

    protected function getEntityType(): string
    {
        return 'serie';
    }
}
