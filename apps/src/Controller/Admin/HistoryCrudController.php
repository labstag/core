<?php

namespace Labstag\Controller\Admin;

use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Field\CollectionField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\FormField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
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

    private function setActionMoveChapter(Actions $actions): void
    {
        $action = Action::new('moveChapter', 'Déplacer un chapitre');
        $action->setHtmlAttributes(
            ['target' => '_blank']
        );
        $action->linkToCrudAction('moveChapter');
        $action->displayIf(static fn ($entity) => is_null($entity->getDeletedAt()));

        $actions->add(Crud::PAGE_DETAIL, $action);
        $actions->add(Crud::PAGE_EDIT, $action);
        $actions->add(Crud::PAGE_INDEX, $action);
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
}
