<?php

namespace Labstag\Controller\Admin;

use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Filter\EntityFilter;
use Labstag\Entity\Meta;
use Labstag\Entity\Page;
use Labstag\Form\Paragraphs\PageType;
use Labstag\Lib\AbstractCrudControllerLib;
use Override;
use Symfony\Component\Translation\TranslatableMessage;

class PageCrudController extends AbstractCrudControllerLib
{
    #[Override]
    public function configureActions(Actions $actions): Actions
    {
        $this->setActionPublic($actions);
        $this->setEditDetail($actions);
        $this->configureActionsTrash($actions);

        return $actions;
    }

    #[Override]
    public function configureCrud(Crud $crud): Crud
    {
        $crud = parent::configureCrud($crud);
        $crud->setDefaultSort(
            ['createdAt' => 'DESC']
        );

        return $crud;
    }

    #[Override]
    public function configureFields(string $pageName): iterable
    {
        $currentEntity = $this->getContext()->getEntity()->getInstance();
        yield $this->addTabPrincipal();
        yield $this->addFieldID();
        yield $this->addFieldIDShortcode('page');
        if ($currentEntity instanceof Page && $currentEntity->getType() != 'home') {
            yield $this->addFieldSlug();
        }

        yield $this->addFieldBoolean('enable', new TranslatableMessage('Enable'));
        $fieldChoice = $this->addFieldIsHome($currentEntity, $pageName);
        if ($fieldChoice instanceof ChoiceField) {
            yield $fieldChoice;
        }

        yield $this->addFieldTitle();
        yield AssociationField::new('page', new TranslatableMessage('Page'))->autocomplete();
        yield $this->addCreatedAtField();
        yield $this->addUpdatedAtField();
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

        yield $this->addFieldWorkflow();
        yield $this->addFieldState();
    }

    #[Override]
    public function configureFilters(Filters $filters): Filters
    {
        $this->addFilterRefUser($filters);
        $this->addFilterEnable($filters);
        $filters->add(EntityFilter::new('page', new TranslatableMessage('Page')));
        $this->addFilterTags($filters);
        $this->addFilterCategories($filters);

        return $filters;
    }

    #[Override]
    public function createEntity(string $entityFqcn): Page
    {
        $page = new $entityFqcn();
        $this->workflowService->init($page);
        $meta = new Meta();
        $page->setMeta($meta);
        $home = $this->getRepository()->findOneBy(
            ['type' => 'home']
        );
        if ($home instanceof Page) {
            $page->setPage($home);
        }

        $page->setType(($home instanceof Page) ? 'page' : 'home');
        $page->setRefuser($this->getUser());

        return $page;
    }

    #[Override]
    public static function getEntityFqcn(): string
    {
        return Page::class;
    }

    protected function addFieldIsHome(?Page $page, string $pageName): ?ChoiceField
    {
        if ($pageName === 'new' || ($page instanceof Page && $page->getType() == 'home')) {
            return null;
        }

        $choiceField = ChoiceField::new('type', new TranslatableMessage('Type'));
        $choiceField->setChoices($this->siteService->getTypesPages());
        $choiceField->setRequired(true);

        return $choiceField;
    }
}
