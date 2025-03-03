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
use Labstag\Field\WysiwygField;
use Labstag\Lib\AbstractCrudControllerLib;
use Labstag\Repository\PageRepository;
use Override;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Translation\TranslatableMessage;

class PageCrudController extends AbstractCrudControllerLib
{
    #[Override]
    public function configureActions(Actions $actions): Actions
    {
        $this->setActionPublic($actions, 'admin_page_w3c', 'admin_page_public');
        $this->setEditDetail($actions);
        $this->configureActionsTrash($actions);

        return $actions;
    }

    #[Route('/admin/page/{entity}/w3c', name: 'admin_page_w3c')]
    public function w3c(string $entity, PageRepository $pageRepository): RedirectResponse
    {
        $page = $pageRepository->find($entity);

        return $this->linkw3CValidator($page);
    }

    #[Route('/admin/page/{entity}/public', name: 'admin_page_public')]
    protected function linkPublic(string $entity, ?PageRepository $pageRepository = null): RedirectResponse
    {
        $page = $pageRepository->find($entity);

        return $this->linkPublic($page);
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
        if ($currentEntity instanceof Page && 'home' != $currentEntity->getType()) {
            yield $this->addFieldSlug();
        }

        yield $this->addFieldBoolean('enable', new TranslatableMessage('Enable'));
        $fieldChoice = $this->addFieldIsHome($currentEntity, $pageName);
        if ($fieldChoice instanceof ChoiceField) {
            yield $fieldChoice;
        }

        yield $this->addFieldTitle();
        yield AssociationField::new('page', new TranslatableMessage('Page'))->autocomplete();
        yield $this->addFieldImageUpload('img', $pageName);
        yield $this->addFieldTags('page');
        yield $this->addFieldCategories('page');
        yield WysiwygField::new('resume', new TranslatableMessage('resume'))->hideOnIndex();
        $fields = array_merge(
            $this->addFieldParagraphs($pageName),
            $this->addFieldMetas(),
            $this->addFieldRefUser()
        );
        foreach ($fields as $field) {
            yield $field;
        }

        yield $this->addFieldWorkflow();
        yield $this->addFieldState();
        $date = $this->addTabDate();
        foreach ($date as $field) {
            yield $field;
        }
    }

    #[Override]
    public function configureFilters(Filters $filters): Filters
    {
        $this->addFilterRefUser($filters);
        $this->addFilterEnable($filters);
        $filters->add(EntityFilter::new('page', new TranslatableMessage('Page')));
        $this->addFilterTags($filters, 'page');
        $this->addFilterCategories($filters, 'page');

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
        if ('new' === $pageName || ($page instanceof Page && 'home' == $page->getType())) {
            return null;
        }

        $choiceField = ChoiceField::new('type', new TranslatableMessage('Type'));
        $choiceField->setChoices($this->siteService->getTypesPages());
        $choiceField->setRequired(true);

        return $choiceField;
    }
}
