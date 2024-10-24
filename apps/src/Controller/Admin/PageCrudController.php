<?php

namespace Labstag\Controller\Admin;

use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\FormField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use Labstag\Entity\Meta;
use Labstag\Entity\Page;
use Labstag\Form\Paragraphs\PageType;
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
        $currentEntity = $this->getContext()->getEntity()->getInstance();
        yield FormField::addTab('Principal');
        yield $this->addFieldID();
        if ($currentEntity instanceof Page && 'home' != $currentEntity->getType()) {
            yield $this->addFieldSlug();
        }

        yield $this->addFieldBoolean();
        $fieldChoice = $this->addFieldIsHome($currentEntity, $pageName);
        if (!is_null($fieldChoice)) {
            yield $fieldChoice;
        }

        yield TextField::new('title');
        yield AssociationField::new('page')->autocomplete();
        yield DateTimeField::new('createdAt')->hideOnForm();
        yield DateTimeField::new('updatedAt')->hideOnForm();
        yield $this->addFieldImageUpload('img', $pageName);
        yield $this->addFieldTags('page');
        yield $this->addFieldCategories('page');
        $fields = array_merge(
            $this->addFieldParagraphs($pageName, PageType::class),
            $this->addFieldMetas(),
            $this->addFieldRefUser()
        );
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
        $home = $this->getRepository()->findOneBy(['type' => 'home']);
        if ($home instanceof Page) {
            $page->setPage($home);
        }

        $page->setType(($home instanceof Page) ? 'page' : 'home');
        $page->setRefuser($this->getUser());
        $page->setMeta($meta);

        return $page;
    }

    #[Override]
    public static function getEntityFqcn(): string
    {
        return Page::class;
    }

    protected function addFieldIsHome($currentEntity, $pageName)
    {
        if ('new' == $pageName && !$currentEntity instanceof Page && 'home' == $currentEntity->getType()) {
            return null;
        }

        $choiceField = ChoiceField::new('type');
        $choiceField->setChoices($this->siteService->getTypesPages());
        $choiceField->setRequired(true);

        return $choiceField;
    }
}
