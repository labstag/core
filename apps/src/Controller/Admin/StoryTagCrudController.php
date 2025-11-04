<?php

namespace Labstag\Controller\Admin;

use EasyCorp\Bundle\EasyAdminBundle\Field\CollectionField;
use Labstag\Entity\StoryTag;
use Symfony\Component\Translation\TranslatableMessage;

class StoryTagCrudController extends TagCrudControllerAbstract
{
    #[\Override]
    public function configureFields(string $pageName): iterable
    {
        $this->configureFieldsDefault();
        $collectionField = CollectionField::new('stories', new TranslatableMessage('Stories'));
        $collectionField->formatValue(fn ($entity): int => count($entity));
        $collectionField->hideOnForm();

        $this->crudFieldFactory->addFieldsToTab('principal', [$collectionField]);

        yield from $this->crudFieldFactory->getConfigureFields($pageName);
    }

    #[\Override]
    public static function getEntityFqcn(): string
    {
        return StoryTag::class;
    }
}
