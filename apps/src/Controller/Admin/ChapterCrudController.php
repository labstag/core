<?php

namespace Labstag\Controller\Admin;

use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\FormField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use Labstag\Entity\Chapter;
use Labstag\Entity\Meta;
use Labstag\Lib\AbstractCrudControllerLib;
use Override;

class ChapterCrudController extends AbstractCrudControllerLib
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
        unset($pageName);
        yield FormField::addTab('Principal');
        yield $this->addFieldID();
        yield $this->addFieldSlug();
        yield $this->addFieldBoolean();
        yield TextField::new('title');
        yield AssociationField::new('refhistory')->autocomplete();
        $fields = $this->addFieldMetas();
        foreach ($fields as $field) {
            yield $field;
        }
    }

    #[Override]
    public function createEntity(string $entityFqcn)
    {
        $chapter = new $entityFqcn();
        $meta    = new Meta();
        $chapter->setMeta($meta);

        return $chapter;
    }

    #[Override]
    public static function getEntityFqcn(): string
    {
        return Chapter::class;
    }
}
