<?php

namespace Labstag\Controller\Admin;

use Override;
use Doctrine\Persistence\Mapping\ClassMetadata;
use Doctrine\Persistence\ObjectManager;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\CollectionField;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use Labstag\Entity\Chapter;
use Labstag\Entity\Story;
use Labstag\Field\WysiwygField;
use Labstag\Message\StoryAllMessage;
use Labstag\Message\StoryMessage;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Translation\TranslatableMessage;

class StoryCrudController extends CrudControllerAbstract
{

    public function chaptersField(): AssociationField
    {
        $associationField = AssociationField::new('chapters', new TranslatableMessage('Chapters'));
        $associationField->setTemplatePath('admin/field/chapters.html.twig');

        return $associationField;
    }

    /**
     * Page-aware variant to avoid AssociationConfigurator errors on index/detail pages.
     * - On index/detail: always return a read-only CollectionField (count/list via template).
     * - On edit/new: only return an AssociationField if Doctrine metadata confirms the association,
     *   otherwise hide the field on forms (no-op for safety).
     */
    public function chaptersFieldForPage(string $entityFqcn, string $pageName): AssociationField|CollectionField
    {
        $associationField = $this->chaptersField();
        // Always safe on listing/detail pages: no AssociationField to configure
        if (in_array($pageName, [Crud::PAGE_INDEX, Crud::PAGE_DETAIL], true)
        ) {
            $associationField->hideOnForm();

            return $associationField;
        }

        // For edit/new pages, check the real Doctrine association
        $entityManager       = $this->managerRegistry->getManagerForClass($entityFqcn);
        $metadata            = $entityManager instanceof ObjectManager ? $entityManager->getClassMetadata(
            $entityFqcn
        ) : null;

        if ($metadata instanceof ClassMetadata && $metadata->hasAssociation('chapters')) {
            $associationField->autocomplete();
            $associationField->setFormTypeOption('by_reference', false);

            return $associationField;
        }

        // No association: ensure nothing is rendered on the form
        $associationField->hideOnForm();

        return $associationField;
    }

    #[Override]
    public function configureActions(Actions $actions): Actions
    {
        $this->actionsFactory->init($actions, self::getEntityFqcn(), static::class);
        $this->setActionMoveChapter();
        $this->setActionNewChapter();
        $this->setUpdateAction();
        $this->actionsFactory->setActionUpdateAll('updateAllStory');

        return $this->actionsFactory->show();
    }

    #[Override]
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

    #[Override]
    public function configureFields(string $pageName): iterable
    {
        $this->crudFieldFactory->setTabPrincipal($this->getContext());
        $wysiwygField = WysiwygField::new('resume', new TranslatableMessage('resume'));
        $wysiwygField->hideOnIndex();

        $this->crudFieldFactory->addFieldsToTab(
            'principal',
            [
                $this->crudFieldFactory->slugField(),
                $this->crudFieldFactory->booleanField('enable', new TranslatableMessage('Enable')),
                $this->crudFieldFactory->titleField(),
                $this->crudFieldFactory->imageField('img', $pageName, self::getEntityFqcn()),
                $this->chaptersFieldForPage(self::getEntityFqcn(), $pageName),
                $wysiwygField,
            ]
        );

        $this->crudFieldFactory->addFieldsToTab(
            'principal',
            $this->crudFieldFactory->taxonomySet(self::getEntityFqcn(), $pageName)
        );
        $this->crudFieldFactory->setTabDate($pageName);

        yield from $this->crudFieldFactory->getConfigureFields($pageName);
    }

    #[Override]
    public function configureFilters(Filters $filters): Filters
    {
        $this->crudFieldFactory->addFilterRefUserFor($filters, self::getEntityFqcn());
        $this->crudFieldFactory->addFilterEnable($filters);
        $this->crudFieldFactory->addFilterTagsFor($filters, self::getEntityFqcn());
        $this->crudFieldFactory->addFilterCategoriesFor($filters, self::getEntityFqcn());

        return $filters;
    }

    public static function getEntityFqcn(): string
    {
        return Story::class;
    }

    public function moveChapter(Request $request): RedirectResponse|Response
    {
        $entityId   = $request->query->get('entityId');
        $story      = $this->getRepository(Story::class)->find($entityId);
        $generator  = $this->container->get(AdminUrlGenerator::class);
        if ($request->isMethod('POST')) {
            $chapters   = $request->get('chapter');
            foreach ($chapters as $id => $position) {
                $chapter = $this->getRepository(Chapter::class)->find($id);
                if (!$chapter instanceof Chapter) {
                    continue;
                }

                $chapter->setPosition($position);
                $this->getRepository(Chapter::class)->persist($chapter);
            }

            $this->getRepository(Chapter::class)->flush();
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

    public function updateAllStory(): RedirectResponse
    {
        $this->messageBus->dispatch(new StoryAllMessage());
        return $this->redirectToRoute('admin_story_index');
    }

    public function updateStory(
        Request $request,
    ): RedirectResponse
    {
        $entityId = $request->query->get('entityId');
        $repositoryAbstract              = $this->getRepository();
        $story                           = $repositoryAbstract->find($entityId);
        $this->messageBus->dispatch(new StoryMessage($story->getId()));
        if ($request->headers->has('referer')) {
            $url = $request->headers->get('referer');
            if (is_string($url) && '' !== $url) {
                return $this->redirect($url);
            }
        }

        return $this->redirectToRoute('admin_story_index');
    }

    private function setActionMoveChapter(): void
    {
        if (!$this->actionsFactory->isTrash()) {
            return;
        }

        $action = Action::new('moveChapter', new TranslatableMessage('Move a chapter'));
        $action->linkToCrudAction('moveChapter');
        $action->displayIf(static fn ($entity): bool => is_null($entity->getDeletedAt()));

        $this->actionsFactory->add(Crud::PAGE_DETAIL, $action);
        $this->actionsFactory->add(Crud::PAGE_EDIT, $action);
        $this->actionsFactory->add(Crud::PAGE_INDEX, $action);
    }

    private function setActionNewChapter(): void
    {
        if (!$this->actionsFactory->isTrash()) {
            return;
        }

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

        $this->actionsFactory->add(Crud::PAGE_DETAIL, $action);
        $this->actionsFactory->add(Crud::PAGE_EDIT, $action);
        $this->actionsFactory->add(Crud::PAGE_INDEX, $action);
    }

    private function setUpdateAction(): void
    {
        if (!$this->actionsFactory->isTrash()) {
            return;
        }

        $action = Action::new('updateStory', new TranslatableMessage('Update'));
        $action->linkToCrudAction('updateStory');
        $action->displayIf(static fn ($entity): bool => is_null($entity->getDeletedAt()));

        $this->actionsFactory->add(Crud::PAGE_DETAIL, $action);
        $this->actionsFactory->add(Crud::PAGE_EDIT, $action);
        $this->actionsFactory->add(Crud::PAGE_INDEX, $action);
    }
}
