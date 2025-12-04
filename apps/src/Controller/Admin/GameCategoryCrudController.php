<?php

namespace Labstag\Controller\Admin;

use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use Labstag\Entity\GameCategory;
use Override;
use Symfony\Component\Translation\TranslatableMessage;

class GameCategoryCrudController extends CategoryCrudControllerAbstract
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
        $associationField = AssociationField::new('games', new TranslatableMessage('Games'));
        $associationField->formatValue(fn ($entity): int => count($entity));
        $associationField->hideOnForm();

        $this->crudFieldFactory->addFieldsToTab('principal', [$associationField]);

        yield from $this->crudFieldFactory->getConfigureFields($pageName);
    }

    public static function getEntityFqcn(): string
    {
        return GameCategory::class;
    }
}
