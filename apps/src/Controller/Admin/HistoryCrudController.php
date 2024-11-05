<?php

namespace Labstag\Controller\Admin;

use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
use EasyCorp\Bundle\EasyAdminBundle\Field\CollectionField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\FormField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use Labstag\Entity\Chapter;
use Labstag\Entity\History;
use Labstag\Entity\Meta;
use Labstag\Form\Paragraphs\HistoryType;
use Labstag\Lib\AbstractCrudControllerLib;
use Override;

class HistoryCrudController extends AbstractCrudControllerLib
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
    public function configureFields(string $pageName): iterable
    {
        yield FormField::addTab('Principal');
        yield $this->addFieldID();
        yield $this->addFieldSlug();
        yield $this->addFieldBoolean();
        yield TextField::new('title');
        yield DateTimeField::new('createdAt')->hideOnForm();
        yield DateTimeField::new('updatedAt')->hideOnForm();
        yield $this->addFieldImageUpload('img', $pageName);
        yield $this->addFieldTags('history');
        yield $this->addFieldCategories('history');
        $collectionField = CollectionField::new('chapters');
        $collectionField->onlyOnIndex();
        $collectionField->formatValue(fn ($value) => count($value));
        yield $collectionField;
        $collectionField = CollectionField::new('chapters');
        $collectionField->setTemplatePath('admin/field/chapters.html.twig');
        $collectionField->onlyOnDetail();
        yield $collectionField;
        $fields = array_merge(
            $this->addFieldParagraphs($pageName, HistoryType::class),
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
        $history = new $entityFqcn();
        $this->workflowService->init($history);
        $history->setRefuser($this->getUser());
        $meta = new Meta();
        $history->setMeta($meta);

        return $history;
    }

    #[Override]
    public static function getEntityFqcn(): string
    {
        return History::class;
    }

    public function moveChapter(AdminContext $adminContext)
    {
        $request    = $adminContext->getRequest();
        $repository = $this->getRepository();
        $entityId   = $request->query->get('entityId');
        $history    = $repository->find($entityId);
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
            $this->addFlash('success', 'Position mise à jour');

            $url = $generator->setController(static::class)->setAction(Action::INDEX)->generateUrl();

            return $this->redirect($url);
        }

        return $this->render(
            'admin/history/order.html.twig',
            [
                'chapters' => $history->getChapters(),
            ]
        );
    }

    private function setActionMoveChapter(Actions $actions): void
    {
        $action = Action::new('moveChapter', 'Déplacer un chapitre');
        $action->linkToCrudAction('moveChapter');
        $action->displayIf(static fn ($entity) => is_null($entity->getDeletedAt()));

        $actions->add(Crud::PAGE_DETAIL, $action);
        $actions->add(Crud::PAGE_EDIT, $action);
        $actions->add(Crud::PAGE_INDEX, $action);
    }
}
