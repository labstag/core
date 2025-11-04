<?php

namespace Labstag\Controller\Admin;

use EasyCorp\Bundle\EasyAdminBundle\Field\CollectionField;
use Labstag\Entity\PostCategory;
use Symfony\Component\Translation\TranslatableMessage;

class PostCategoryCrudController extends CategoryCrudControllerAbstract
{
    #[\Override]
    public function configureFields(string $pageName): iterable
    {
        $this->crudFieldFactory->setTabPrincipal(self::getEntityFqcn());
        $titleField = $this->crudFieldFactory->titleField();
        $titleField->setFormattedValue(
            function($entity) {
                return $entity->getTitle() ?? (new TranslatableMessage('Label not found'));
            }
        );
        $this->crudFieldFactory->addFieldsToTab(
            'principal',
            [
                $this->crudFieldFactory->slugField(),
                $titleField,
            ]
        );
        $collectionField = CollectionField::new('posts', new TranslatableMessage('Posts'));
        $collectionField->formatValue(fn ($entity): int => count($entity));
        $collectionField->hideOnForm();

        $this->crudFieldFactory->addFieldsToTab('principal', [$collectionField]);

        yield from $this->crudFieldFactory->getConfigureFields($pageName);
    }

    public static function getEntityFqcn(): string
    {
        return PostCategory::class;
    }
}
