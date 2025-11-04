<?php

namespace Labstag\Controller\Admin;

use EasyCorp\Bundle\EasyAdminBundle\Field\CollectionField;
use Labstag\Entity\SerieCategory;
use Symfony\Component\Translation\TranslatableMessage;

class SerieCategoryCrudController extends CategoryCrudControllerAbstract
{
    #[\Override]
    public function configureFields(string $pageName): iterable
    {
        $this->crudFieldFactory->setTabPrincipal(self::getEntityFqcn());
        $titleField = $this->crudFieldFactory->titleField();
        $titleField->setFormattedValue(
            function($entity) {
                return $entity->getTitle() ?? (new TranslatableMessage('label.no_title'));
            }
        );
        $this->crudFieldFactory->addFieldsToTab(
            'principal',
            [
                $this->crudFieldFactory->slugField(),
                $titleField,
            ]
        );
        $collectionField = CollectionField::new('series', new TranslatableMessage('Series'));
        $collectionField->formatValue(fn ($entity): int => count($entity));
        $collectionField->hideOnForm();

        $this->crudFieldFactory->addFieldsToTab('principal', [$collectionField]);

        yield from $this->crudFieldFactory->getConfigureFields($pageName);
    }

    public static function getEntityFqcn(): string
    {
        return SerieCategory::class;
    }
}
