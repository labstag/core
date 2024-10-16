<?php

namespace Labstag\Controller\Admin;

use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use Labstag\Entity\Memo;
use Labstag\Form\Paragraphs\MemoType;
use Labstag\Lib\AbstractCrudControllerLib;
use Override;

class MemoCrudController extends AbstractCrudControllerLib
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
        yield $this->addFieldID();
        yield $this->addFieldSlug();
        yield $this->addFieldBoolean();
        yield TextField::new('title');
        yield DateTimeField::new('createdAt')->hideOnForm();
        yield DateTimeField::new('updatedAt')->hideOnForm();
        yield $this->addFieldImageUpload('img', $pageName);
        yield $this->addFieldTags('memo');
        $fields = array_merge(
            $this->addFieldParagraphs($pageName, MemoType::class),
            $this->addFieldRefUser()
        );
        foreach ($fields as $field) {
            yield $field;
        }
    }

    #[Override]
    public function createEntity(string $entityFqcn)
    {
        $memo = new $entityFqcn();
        $this->workflowService->init($memo);
        $memo->setRefuser($this->getUser());

        return $memo;
    }

    #[Override]
    public static function getEntityFqcn(): string
    {
        return Memo::class;
    }
}
