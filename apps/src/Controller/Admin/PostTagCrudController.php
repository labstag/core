<?php

namespace Labstag\Controller\Admin;

use Override;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use Labstag\Entity\PostTag;
use Symfony\Component\Translation\TranslatableMessage;

class PostTagCrudController extends TagCrudControllerAbstract
{
    #[Override]
    public function configureFields(string $pageName): iterable
    {
        $this->crudFieldFactory->setTabPrincipal($this->getContext());
        $this->crudFieldFactory->addFieldsToTab('principal', [$this->crudFieldFactory->titleField()]);

        $associationField = AssociationField::new('posts', new TranslatableMessage('Posts'));
        $associationField->formatValue(fn ($entity): int => count($entity));
        $associationField->hideOnForm();

        $this->crudFieldFactory->addFieldsToTab('principal', [$associationField]);

        yield from $this->crudFieldFactory->getConfigureFields($pageName);
    }

    #[Override]
    public static function getEntityFqcn(): string
    {
        return PostTag::class;
    }
}
