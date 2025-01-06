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
use Labstag\Entity\Meta;
use Labstag\Entity\Story;
use Labstag\Form\Paragraphs\StoryType;
use Labstag\Lib\AbstractCrudControllerLib;
use Override;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Translation\TranslatableMessage;

class StoryCrudController extends AbstractCrudControllerLib
{
    #[Override]
    public function configureActions(Actions $actions): Actions
    {
        $this->setActionPublic($actions);
        $this->setEditDetail($actions);
        $this->configureActionsTrash($actions);
        $this->setActionMoveChapter($actions);

        return $actions;
    }

    #[Override]
    public function configureCrud(Crud $crud): Crud
    {
        $crud->setDefaultSort(
            ['createdAt' => 'DESC']
        );

        return $crud;
    }

    #[Override]
    public function configureFields(string $pageName): iterable
    {
        yield $this->addTabPrincipal();
        yield $this->addFieldID();
        yield $this->addFieldIDShortcode('story');
        yield $this->addFieldSlug();
        yield $this->addFieldBoolean();
        yield $this->addFieldTitle();
        yield $this->addCreatedAtField();
        yield $this->addUpdatedAtField();
        yield $this->addFieldImageUpload('img', $pageName);
        yield $this->addFieldTags('story');
        yield $this->addFieldCategories('story');
        $collectionField = CollectionField::new('chapters');
        $collectionField->onlyOnIndex();
        $collectionField->formatValue(fn ($value): int => count($value));
        yield $collectionField;
        $collectionField = CollectionField::new('chapters', new TranslatableMessage('Chapters'));
        $collectionField->setTemplatePath('admin/field/chapters.html.twig');
        $collectionField->onlyOnDetail();
        yield $collectionField;
        $fields = array_merge(
            $this->addFieldParagraphs($pageName, StoryType::class),
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

        return $filters;
    }

    #[Override]
    public function createEntity(string $entityFqcn)
    {
        $story = new $entityFqcn();
        $this->workflowService->init($story);
        $story->setRefuser($this->getUser());
        $meta = new Meta();
        $story->setMeta($meta);

        return $story;
    }

    #[Override]
    public static function getEntityFqcn(): string
    {
        return Story::class;
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

    private function setActionMoveChapter(Actions $actions): void
    {
        $action = Action::new('moveChapter', new TranslatableMessage('Move a chapter'));
        $action->linkToCrudAction('moveChapter');
        $action->displayIf(static fn ($entity): bool => is_null($entity->getDeletedAt()));

        $actions->add(Crud::PAGE_DETAIL, $action);
        $actions->add(Crud::PAGE_EDIT, $action);
        $actions->add(Crud::PAGE_INDEX, $action);
    }
}
