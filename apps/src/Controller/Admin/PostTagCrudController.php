<?php

namespace Labstag\Controller\Admin;

use EasyCorp\Bundle\EasyAdminBundle\Field\CollectionField;
use Labstag\Entity\PostTag;
use Symfony\Component\Translation\TranslatableMessage;

class PostTagCrudController extends TagCrudControllerAbstract
{
    #[\Override]
    public function configureFields(string $pageName): iterable
    {
        $this->configureFieldsDefault();
        $collectionField = CollectionField::new('posts', new TranslatableMessage('Posts'));
        $collectionField->formatValue(fn ($entity): int => count($entity));
        $collectionField->hideOnForm();

        $this->crudFieldFactory->addFieldsToTab('principal', [$collectionField]);

        yield from $this->crudFieldFactory->getConfigureFields($pageName);
    }

    #[\Override]
    public static function getEntityFqcn(): string
    {
        return PostTag::class;
    }
}
