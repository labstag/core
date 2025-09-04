<?php

namespace Labstag\Controller\Admin;

use Labstag\Entity\Saga;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Field\CollectionField;
use Labstag\Lib\AbstractCrudControllerLib;
use Override;
use Symfony\Component\Translation\TranslatableMessage;

class SagaCrudController extends AbstractCrudControllerLib
{
    public static function getEntityFqcn(): string
    {
        return Saga::class;
    }

    #[Override]
    public function configureActions(Actions $actions): Actions
    {
        $this->setEditDetail($actions);
        $this->configureActionsBtn($actions);

        return $actions;
    }

    #[Override]
    public function configureCrud(Crud $crud): Crud
    {
        $crud = parent::configureCrud($crud);
        $crud->setDefaultSort(
            ['title' => 'ASC']
        );
        $crud->setEntityLabelInSingular(new TranslatableMessage('Saga'));
        $crud->setEntityLabelInPlural(new TranslatableMessage('Sagas'));

        return $crud;
    }

    #[Override]
    public function configureFields(string $pageName): iterable
    {
        yield $this->addFieldID();
        yield $this->addFieldTitle();
        $collectionField = CollectionField::new('movies', new TranslatableMessage('Movies'));
        $collectionField->onlyOnIndex();
        $collectionField->formatValue(fn ($value): int => count($value));
        yield $collectionField;
        $collectionField = CollectionField::new('movies', new TranslatableMessage('Movies'));
        $collectionField->setTemplatePath('admin/field/movies.html.twig');
        $collectionField->onlyOnDetail();
        yield $collectionField;
    }
}
