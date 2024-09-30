<?php

namespace Labstag\Controller\Admin;

use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\FormField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use Labstag\Entity\Edito;
use Labstag\Entity\Meta;
use Labstag\Lib\AbstractCrudControllerLib;
use Override;

class EditoCrudController extends AbstractCrudControllerLib
{
    #[Override]
    public function configureActions(Actions $actions): Actions
    {
        $this->configureActionsTrash($actions);

        return $actions;
    }

    #[Override]
    public function configureFields(string $pageName): iterable
    {
        unset($pageName);
        yield FormField::addTab('Principal');
        yield $this->addFieldID();
        yield $this->addFieldSlug();
        yield $this->addFieldBoolean();
        yield TextField::new('title');
        yield DateTimeField::new('createdAt')->hideOnForm();
        yield DateTimeField::new('updatedAt')->hideOnForm();
        yield $this->addFieldRefUser();
        $fields = $this->addFieldMetas();
        foreach ($fields as $field) {
            yield $field;
        }
    }

    #[Override]
    public function createEntity(string $entityFqcn)
    {
        $edito = new $entityFqcn();
        $meta  = new Meta();
        $edito->setMeta($meta);

        return $edito;
    }

    #[Override]
    public static function getEntityFqcn(): string
    {
        return Edito::class;
    }
}
