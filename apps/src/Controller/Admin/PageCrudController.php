<?php

namespace Labstag\Controller\Admin;

use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
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
        yield FormField::addTab('Principal');
        yield $this->addFieldID();
        yield $this->addFieldSlug();
        yield $this->addFieldBoolean();
        yield TextField::new('title');
        yield AssociationField::new('page')->autocomplete();
        yield DateTimeField::new('createdAt')->hideOnForm();
        yield DateTimeField::new('updatedAt')->hideOnForm();
        yield $this->addFieldRefUser();
        yield $this->addFieldImageUpload('img', $pageName);
        yield $this->addFieldTags('page');
        yield $this->addFieldCategories('page');
        $fields = $this->addFieldMetas();
        foreach ($fields as $field) {
            yield $field;
        }
    }

    #[Override]
    public function createEntity(string $entityFqcn)
    {
        $page = new $entityFqcn();
        $this->workflowService->init($page);
        $meta = new Meta();
        $page->setRefuser($this->getUser());
        $page->setMeta($meta);

        return $page;
    }

    #[Override]
    public static function getEntityFqcn(): string
    {
        return Page::class;
    }
}
