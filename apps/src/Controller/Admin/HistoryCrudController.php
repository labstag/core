<?php

namespace Labstag\Controller\Admin;

use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\FormField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use Labstag\Entity\History;
use Labstag\Entity\Meta;
use Labstag\Lib\AbstractCrudControllerLib;
use Override;

class HistoryCrudController extends AbstractCrudControllerLib
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
        yield $this->addFieldID();
        yield $this->addFieldSlug();
        yield $this->addFieldBoolean();
        yield TextField::new('title');
        yield DateTimeField::new('createdAt')->hideOnForm();
        yield DateTimeField::new('updatedAt')->hideOnForm();
        yield $this->addFieldRefUser();
        yield FormField::addFieldset('Meta');
        yield TextField::new('meta.title')->hideOnIndex();
        yield TextField::new('meta.keywords')->hideOnIndex();
        yield TextField::new('meta.description')->hideOnIndex();
    }

    #[Override]
    public function createEntity(string $entityFqcn)
    {
        $history = new History();
        $meta    = new Meta();
        $history->setMeta($meta);

        return $history;
    }

    #[Override]
    public static function getEntityFqcn(): string
    {
        return History::class;
    }
}
