<?php

namespace Labstag\Controller\Admin;

use EasyCorp\Bundle\EasyAdminBundle\Field\CollectionField;
use Labstag\Entity\StoryCategory;
use Symfony\Component\Translation\TranslatableMessage;

class StoryCategoryCrudController extends CategoryCrudControllerAbstract
{
    #[\Override]
    public function configureFields(string $pageName): iterable
    {
        $this->crudFieldFactory->setTabPrincipal(self::getEntityFqcn());
        $this->crudFieldFactory->addFieldsToTab(
            'principal',
            [
                $this->crudFieldFactory->slugField(),
                $this->crudFieldFactory->titleField(),
            ]
        );
        $collectionField = CollectionField::new('stories', new TranslatableMessage('Stories'));
        $collectionField->formatValue(fn ($entity): int => count($entity));
        $collectionField->hideOnForm();

        $this->crudFieldFactory->addFieldsToTab('principal', [$collectionField]);

        yield from $this->crudFieldFactory->getConfigureFields($pageName);
    }

    public static function getEntityFqcn(): string
    {
        return StoryCategory::class;
    }
}
