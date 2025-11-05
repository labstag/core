<?php

namespace Labstag\Controller\Admin;

use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use Labstag\Entity\StoryTag;
use Symfony\Component\Translation\TranslatableMessage;

class StoryTagCrudController extends TagCrudControllerAbstract
{
    #[\Override]
    public function configureFields(string $pageName): iterable
    {
        $this->crudFieldFactory->setTabPrincipal(self::getEntityFqcn());
        $this->crudFieldFactory->addFieldsToTab('principal', [$this->crudFieldFactory->titleField()]);

        $associationField = AssociationField::new('stories', new TranslatableMessage('Stories'));
        $associationField->formatValue(fn ($entity): int => count($entity));
        $associationField->hideOnForm();

        $this->crudFieldFactory->addFieldsToTab('principal', [$associationField]);

        yield from $this->crudFieldFactory->getConfigureFields($pageName);
    }

    #[\Override]
    public static function getEntityFqcn(): string
    {
        return StoryTag::class;
    }
}
