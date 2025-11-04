<?php

namespace Labstag\Controller\Admin;

use EasyCorp\Bundle\EasyAdminBundle\Field\CollectionField;
use Labstag\Entity\PageCategory;
use Symfony\Component\Translation\TranslatableMessage;

class PageCategoryCrudController extends CategoryCrudControllerAbstract
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
        $collectionField = CollectionField::new('pages', new TranslatableMessage('Pages'));
        $collectionField->formatValue(fn ($entity): int => count($entity));
        $collectionField->hideOnForm();

        $this->crudFieldFactory->addFieldsToTab('principal', [$collectionField]);

        yield from $this->crudFieldFactory->getConfigureFields($pageName);
    }

    public static function getEntityFqcn(): string
    {
        return PageCategory::class;
    }
}
