<?php

namespace Labstag\Controller\Admin;

use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use Labstag\Entity\MovieCategory;
use Symfony\Component\Translation\TranslatableMessage;

class MovieCategoryCrudController extends CategoryCrudControllerAbstract
{
    #[\Override]
    public function configureFields(string $pageName): iterable
    {
        $this->crudFieldFactory->setTabPrincipal(self::getEntityFqcn());
        $titleField = $this->crudFieldFactory->titleField();
        $titleField->setFormattedValue(
            fn ($entity) => $entity->getTitle() ?? (new TranslatableMessage('Label not found'))
        );
        $this->crudFieldFactory->addFieldsToTab(
            'principal',
            [
                $this->crudFieldFactory->slugField(),
                $titleField,
            ]
        );
        $associationField = AssociationField::new('movies', new TranslatableMessage('Movies'));
        $associationField->formatValue(fn ($entity): int => count($entity));
        $associationField->hideOnForm();

        $this->crudFieldFactory->addFieldsToTab('principal', [$associationField]);
        yield from $this->crudFieldFactory->getConfigureFields($pageName);
    }

    public static function getEntityFqcn(): string
    {
        return MovieCategory::class;
    }
}
