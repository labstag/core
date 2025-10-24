<?php

namespace Labstag\Controller\Admin;

use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
use EasyCorp\Bundle\EasyAdminBundle\Field\CollectionField;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use Labstag\Controller\Admin\Abstract\AbstractCrudControllerLib;
use Labstag\Entity\Chapter;
use Labstag\Entity\Meta;
use Labstag\Entity\Story;
use Labstag\Field\FileField;
use Labstag\Field\WysiwygField;
use Labstag\Service\StoryService;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Translation\TranslatableMessage;

class StoryCrudController extends AbstractCrudControllerLib
{
    #[\Override]
    public function configureActions(Actions $actions): Actions
    {
        $this->setActionPublic($actions, 'admin_story_w3c', 'admin_story_public');
        $this->setEditDetail($actions);
        $this->configureActionsTrash($actions);
        $this->setActionMoveChapter($actions);
        $this->setActionNewChapter($actions);
        $this->configureActionsUpdatePdf($actions);

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
        // Principal tab + standard full content set
        yield $this->addTabPrincipal();
        yield $this->crudFieldFactory->addFieldIDShortcode('story');
        $isSuperAdmin = $this->isSuperAdmin();
        foreach ($this->crudFieldFactory->fullContentSet(
            'story',
            $pageName,
            self::getEntityFqcn(),
            $isSuperAdmin
        ) as $field) {
            yield $field;
        }

        // Extra specific field not part of the generic bundle
        yield FileField::new('pdf', new TranslatableMessage('pdf'));
        $collectionField = CollectionField::new('chapters', new TranslatableMessage('Chapters'));
        $collectionField->onlyOnIndex();
        $collectionField->formatValue(fn ($value): int => count($value));
        yield $collectionField;
        $collectionField = CollectionField::new('chapters', new TranslatableMessage('Chapters'));
        $collectionField->setTemplatePath('admin/field/chapters.html.twig');
        $collectionField->onlyOnDetail();
        yield $collectionField;
        yield WysiwygField::new('resume', new TranslatableMessage('resume'))->hideOnIndex();
        // Workflow + state
        yield $this->crudFieldFactory->workflowField();
        yield $this->crudFieldFactory->stateField();
        // Dates
        foreach ($this->crudFieldFactory->dateSet($pageName) as $field) {
            yield $field;
        }
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

    #[\Override]
    public function createEntity(string $entityFqcn): Story
    {
        $story = new $entityFqcn();
        $this->workflowService->init($story);
        $story->setRefuser($this->getUser());
        $meta = new Meta();
        $story->setMeta($meta);

        return $story;
    }

    public static function getEntityFqcn(): string
    {
        return Story::class;
    }

    #[Route('/admin/story/{entity}/public', name: 'admin_story_public')]
    public function linkPublic(string $entity): RedirectResponse
    {
        $serviceEntityRepositoryLib = $this->getRepository();
        $story                      = $serviceEntityRepositoryLib->find($entity);

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

    #[Route('/admin/updatepdf', name: 'admin_story_updatepdf')]
    public function updatepdf(StoryService $storyService): RedirectResponse
    {
        $serviceEntityRepositoryLib = $this->getRepository();
        $stories                    = $serviceEntityRepositoryLib->findAll();

        $counter = 0;
        $update  = 0;
        foreach ($stories as $story) {
            $status = $storyService->setPdf($story);
            $update = $status ? ++$update : $update;
            ++$counter;

            $serviceEntityRepositoryLib->persist($story);
            $serviceEntityRepositoryLib->flush($counter);
        }

        $this->addFlash('success', $storyService->generateFlashBag());
        $serviceEntityRepositoryLib->flush();

        return $this->redirectToRoute('admin_story_index');
    }

    #[Route('/admin/story/{entity}/w3c', name: 'admin_story_w3c')]
    public function w3c(string $entity): RedirectResponse
    {
        $serviceEntityRepositoryLib = $this->getRepository();
        $story                      = $serviceEntityRepositoryLib->find($entity);

        return $this->linkw3CValidator($story);
    }

    private function configureActionsUpdatePdf(Actions $actions): void
    {
        $request = $this->container->get('request_stack')->getCurrentRequest();
        $action  = $request->query->get('action', null);
        if ('trash' == $action) {
            return;
        }

        $action = Action::new('updatepdf', new TranslatableMessage('Update PDF'), 'fas fa-wrench');
        $action->linkToUrl(fn (): string => $this->generateUrl('admin_story_updatepdf'));
        $action->createAsGlobalAction();

        $actions->add(Crud::PAGE_INDEX, $action);
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
}
