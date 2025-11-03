<?php

namespace Labstag\Controller\Admin;

use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
use EasyCorp\Bundle\EasyAdminBundle\Field\CollectionField;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use Labstag\Entity\Chapter;
use Labstag\Entity\Story;
use Labstag\Field\WysiwygField;
use Labstag\Message\StoryMessage;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Translation\TranslatableMessage;

class StoryCrudController extends CrudControllerAbstract
{
    #[\Override]
    public function configureActions(Actions $actions): Actions
    {
        $this->setActionPublic($actions, 'admin_story_w3c', 'admin_story_public');
        $this->setEditDetail($actions);
        $this->configureActionsTrash($actions);
        $this->setActionMoveChapter($actions);
        $this->setActionNewChapter($actions);
        $action = $this->setUpdateAction();
        $actions->add(Crud::PAGE_DETAIL, $action);
        $actions->add(Crud::PAGE_EDIT, $action);
        $actions->add(Crud::PAGE_INDEX, $action);

        $action = Action::new('updateAll', new TranslatableMessage('Update all'), 'fas fa-sync-alt');
        $action->displayAsLink();
        $action->linkToCrudAction('updateAll');
        $action->createAsGlobalAction();

        $actions->add(Crud::PAGE_INDEX, $action);

        return $actions;
    }

    #[\Override]
    public function configureCrud(Crud $crud): Crud
    {
        $crud = parent::configureCrud($crud);
        $crud->setEntityLabelInSingular(new TranslatableMessage('Story'));
        $crud->setEntityLabelInPlural(new TranslatableMessage('Stories'));
        $crud->setDefaultSort(
            ['createdAt' => 'DESC']
        );

        return $crud;
    }

    #[\Override]
    public function configureFields(string $pageName): iterable
    {
        $this->crudFieldFactory->setTabPrincipal(self::getEntityFqcn());
        $collectionField = CollectionField::new('chapters', new TranslatableMessage('Chapters'));
        $collectionField->setTemplatePath('admin/field/chapters.html.twig');
        $collectionField->hideOnForm();

        $wysiwygField = WysiwygField::new('resume', new TranslatableMessage('resume'));
        $wysiwygField->hideOnIndex();

        $this->crudFieldFactory->addFieldsToTab(
            'principal',
            [
                $this->crudFieldFactory->slugField(),
                $this->crudFieldFactory->booleanField('enable', (string) new TranslatableMessage('Enable')),
                $this->crudFieldFactory->titleField(),
                $this->crudFieldFactory->imageField('img', $pageName, self::getEntityFqcn()),
                $collectionField,
                $wysiwygField,
            ]
        );

        $this->crudFieldFactory->addFieldsToTab('principal', $this->crudFieldFactory->taxonomySet('story'));

        $this->crudFieldFactory->setTabParagraphs($pageName);
        $this->crudFieldFactory->setTabSEO();
        $this->crudFieldFactory->setTabUser($this->isSuperAdmin());
        $this->crudFieldFactory->setTabWorkflow();
        $this->crudFieldFactory->setTabDate($pageName);

        yield from $this->crudFieldFactory->getConfigureFields();
    }

    #[\Override]
    public function configureFilters(Filters $filters): Filters
    {
        $this->crudFieldFactory->addFilterRefUser($filters);
        $this->crudFieldFactory->addFilterEnable($filters);
        $this->crudFieldFactory->addFilterTags($filters, 'story');
        $this->crudFieldFactory->addFilterCategories($filters, 'story');

        return $filters;
    }

    public static function getEntityFqcn(): string
    {
        return Story::class;
    }

    #[Route('/admin/story/{entity}/public', name: 'admin_story_public')]
    public function linkPublic(string $entity): RedirectResponse
    {
        $RepositoryAbstract              = $this->getRepository();
        $story                           = $RepositoryAbstract->find($entity);

        return $this->publicLink($story);
    }

    public function moveChapter(AdminContext $adminContext): RedirectResponse|Response
    {
        $request    = $adminContext->getRequest();
        $repository = $this->getRepository();
        $entityId   = $request->query->get('entityId');
        $story      = $repository->find($entityId);
        $generator  = $this->container->get(AdminUrlGenerator::class);
        if ($request->isMethod('POST')) {
            $repository = $this->getRepository(Chapter::class);
            $chapters   = $request->get('chapter');
            foreach ($chapters as $id => $position) {
                $chapter = $repository->find($id);
                if (!$chapter instanceof Chapter) {
                    continue;
                }

                $chapter->setPosition($position);
                $repository->persist($chapter);
            }

            $repository->flush();
            $this->addFlash('success', new TranslatableMessage('Position updated'));

            $url = $generator->setController(static::class)->setAction(Action::INDEX)->generateUrl();

            return $this->redirect($url);
        }

        return $this->render(
            'admin/story/order.html.twig',
            [
                'chapters' => $story->getChapters(),
            ]
        );
    }

    #[Route('/admin/story/{entity}/update', name: 'admin_story_update')]
    public function update(string $entity, Request $request, MessageBusInterface $messageBus): RedirectResponse
    {
        $RepositoryAbstract              = $this->getRepository();
        $story                           = $RepositoryAbstract->find($entity);
        $messageBus->dispatch(new StoryMessage($story->getId()));
        if ($request->headers->has('referer')) {
            $url = $request->headers->get('referer');
            if (is_string($url) && '' !== $url) {
                return $this->redirect($url);
            }
        }

        return $this->redirectToRoute('admin_story_index');
    }

    public function updateAll(MessageBusInterface $messageBus): RedirectResponse
    {
        $RepositoryAbstract               = $this->getRepository();
        $stories                          = $RepositoryAbstract->findAll();
        foreach ($stories as $story) {
            $messageBus->dispatch(new StoryMessage($story->getId()));
        }

        return $this->redirectToRoute('admin_story_index');
    }

    #[Route('/admin/story/{entity}/w3c', name: 'admin_story_w3c')]
    public function w3c(string $entity): RedirectResponse
    {
        $RepositoryAbstract              = $this->getRepository();
        $story                           = $RepositoryAbstract->find($entity);

        return $this->linkw3CValidator($story);
    }

    private function setActionMoveChapter(Actions $actions): void
    {
        $action = Action::new('moveChapter', new TranslatableMessage('Move a chapter'));
        $action->linkToCrudAction('moveChapter');
        $action->displayIf(static fn ($entity): bool => is_null($entity->getDeletedAt()));

        $actions->add(Crud::PAGE_DETAIL, $action);
        $actions->add(Crud::PAGE_EDIT, $action);
        $actions->add(Crud::PAGE_INDEX, $action);
    }

    private function setActionNewChapter(Actions $actions): void
    {
        $action = Action::new('newChapter', new TranslatableMessage('New chapter'));
        $action->linkToUrl(
            fn (Story $story): string => $this->generateUrl(
                'admin_chapter_new',
                [
                    'story' => $story->getId(),
                ]
            )
        );
        $action->displayIf(static fn ($entity): bool => is_null($entity->getDeletedAt()));

        $actions->add(Crud::PAGE_DETAIL, $action);
        $actions->add(Crud::PAGE_EDIT, $action);
        $actions->add(Crud::PAGE_INDEX, $action);
    }

    private function setUpdateAction(): Action
    {
        $action = Action::new('update', new TranslatableMessage('Update'));
        $action->linkToUrl(
            fn (Story $story): string => $this->generateUrl(
                'admin_story_update',
                [
                    'entity' => $story->getId(),
                ]
            )
        );
        $action->displayIf(static fn ($entity): bool => is_null($entity->getDeletedAt()));

        return $action;
    }
}
