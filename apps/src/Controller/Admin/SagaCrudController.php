<?php

namespace Labstag\Controller\Admin;

use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Field\CollectionField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use Labstag\Controller\Admin\Abstract\AbstractCrudControllerLib;
use Labstag\Entity\Saga;
use Labstag\Field\WysiwygField;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Translation\TranslatableMessage;

class SagaCrudController extends AbstractCrudControllerLib
{
    #[\Override]
    public function configureActions(Actions $actions): Actions
    {
        $this->setEditDetail($actions);
        $this->configureActionsBtn($actions);
        $action = $this->setLinkTmdbAction();
        $actions->add(Crud::PAGE_DETAIL, $action);
        $actions->add(Crud::PAGE_EDIT, $action);
        $actions->add(Crud::PAGE_INDEX, $action);

        return $actions;
    }

    #[\Override]
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

    #[\Override]
    public function configureFields(string $pageName): iterable
    {
        foreach ($this->crudFieldFactory->baseIdentitySet(
            'saga',
            $pageName,
            self::getEntityFqcn(),
            withEnable: false
        ) as $field) {
            yield $field;
        }

        yield TextField::new('tmdb', new TranslatableMessage('Tmdb'));
        $collectionField = CollectionField::new('movies', new TranslatableMessage('Movies'));
        $collectionField->onlyOnIndex();
        $collectionField->formatValue(fn ($value): int => count($value));
        yield $collectionField;
        yield WysiwygField::new('description', new TranslatableMessage('Description'))->hideOnIndex();
        $collectionField = CollectionField::new('movies', new TranslatableMessage('Movies'));
        $collectionField->setTemplatePath('admin/field/movies.html.twig');
        $collectionField->onlyOnDetail();
        yield $collectionField;
    }

    public static function getEntityFqcn(): string
    {
        return Saga::class;
    }

    #[Route('/admin/saga/{entity}/imdb', name: 'admin_saga_tmdb')]
    public function tmdb(string $entity): RedirectResponse
    {
        $serviceEntityRepositoryLib = $this->getRepository();
        $saga                       = $serviceEntityRepositoryLib->find($entity);

        return $this->redirect('https://www.themoviedb.org/collection/' . $saga->getTmdb());
    }

    private function setLinkTmdbAction(): Action
    {
        $action = Action::new('tmdb', new TranslatableMessage('TMDB Page'));
        $action->setHtmlAttributes(
            ['target' => '_blank']
        );
        $action->linkToUrl(
            fn (Saga $saga): string => $this->generateUrl(
                'admin_saga_tmdb',
                [
                    'entity' => $saga->getId(),
                ]
            )
        );

        return $action;
    }
}
