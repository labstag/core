<?php

namespace Labstag\Controller\Admin;

use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\FormField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use Labstag\Entity\Meta;
use Labstag\Entity\Page;
use Labstag\Lib\AbstractCrudControllerLib;
use Override;

class PageCrudController extends AbstractCrudControllerLib
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
        $page = new $entityFqcn();
        $meta = new Meta();
        $page->setMeta($meta);

        return $page;
    }

    #[Override]
    public static function getEntityFqcn(): string
    {
        return Page::class;
    }
}
