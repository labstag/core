<?php

namespace Labstag\Controller\Admin;

use EasyCorp\Bundle\EasyAdminBundle\Field\CollectionField;
use Labstag\Entity\PageTag;
use Symfony\Component\Translation\TranslatableMessage;

class PageTagCrudController extends TagCrudControllerAbstract
{
    #[\Override]
    public function configureFields(string $pageName): iterable
    {
        $this->configureFieldsDefault();
        $collectionField = CollectionField::new('pages', new TranslatableMessage('Pages'));
        $collectionField->formatValue(fn ($entity): int => count($entity));
        $collectionField->hideOnForm();

        $this->crudFieldFactory->addFieldsToTab('principal', [$collectionField]);
        yield from $this->crudFieldFactory->getConfigureFields($pageName);
    }

    #[\Override]
    public static function getEntityFqcn(): string
    {
        return PageTag::class;
    }
}
