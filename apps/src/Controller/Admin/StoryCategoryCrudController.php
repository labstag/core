<?php

namespace Labstag\Controller\Admin;

use Override;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use Labstag\Entity\StoryCategory;
use Symfony\Component\Translation\TranslatableMessage;

class StoryCategoryCrudController extends CategoryCrudControllerAbstract
{
    #[Override]
    public function configureFields(string $pageName): iterable
    {
        $this->crudFieldFactory->setTabPrincipal($this->getContext());
        $this->crudFieldFactory->addFieldsToTab(
            'principal',
            [
                $this->crudFieldFactory->slugField(),
                $this->crudFieldFactory->titleField(),
            ]
        );
        $associationField = AssociationField::new('stories', new TranslatableMessage('Stories'));
        $associationField->formatValue(fn ($entity): int => count($entity));
        $associationField->hideOnForm();

        $this->crudFieldFactory->addFieldsToTab('principal', [$associationField]);

        yield from $this->crudFieldFactory->getConfigureFields($pageName);
    }

    public static function getEntityFqcn(): string
    {
        return StoryCategory::class;
    }
}
