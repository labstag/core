<?php

namespace Labstag\Controller\Admin;

use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\NumberField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Filter\EntityFilter;
use Labstag\Entity\Movie;
use Labstag\Field\WysiwygField;
use Labstag\Lib\AbstractCrudControllerLib;
use Override;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Intl\Countries;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Translation\TranslatableMessage;

class MovieCrudController extends AbstractCrudControllerLib
{
    #[Override]
    public function configureActions(Actions $actions): Actions
    {
        $actions->add(Crud::PAGE_NEW, Action::SAVE_AND_CONTINUE);

        $action = $this->setLinkImdbAction();
        $actions->add(Crud::PAGE_DETAIL, $action);
        $actions->add(Crud::PAGE_EDIT, $action);
        $actions->add(Crud::PAGE_INDEX, $action);

        $action = $this->setLinkTmdbAction();
        $actions->add(Crud::PAGE_DETAIL, $action);
        $actions->add(Crud::PAGE_EDIT, $action);
        $actions->add(Crud::PAGE_INDEX, $action);

        $action = $this->setUpdateAction();
        $actions->add(Crud::PAGE_DETAIL, $action);
        $actions->add(Crud::PAGE_EDIT, $action);
        $actions->add(Crud::PAGE_INDEX, $action);
        $this->setEditDetail($actions);
        $this->configureActionsTrash($actions);
        $this->configureActionsUpdateImage();

        return $actions;
    }

    #[Override]
    public function configureCrud(Crud $crud): Crud
    {
        $crud = parent::configureCrud($crud);
        $crud->setDefaultSort(
            ['title' => 'ASC']
        );

        return $crud;
    }

    #[Override]
    public function configureFields(string $pageName): iterable
    {
        yield $this->addTabPrincipal();
        yield $this->addFieldID();
        yield $this->addFieldTitle();
        yield TextField::new('imdb', new TranslatableMessage('Imdb'))->hideOnIndex();
        yield TextField::new('tmdb', new TranslatableMessage('Tmdb'))->hideOnIndex();
        yield DateField::new('releaseDate', new TranslatableMessage('Release date'));
        $choiceField = ChoiceField::new('countries', new TranslatableMessage('Countries'));
        $choiceField->setChoices(array_flip(Countries::getNames()));
        $choiceField->allowMultipleChoices();
        $choiceField->renderExpanded(false);

        yield $choiceField;
        yield IntegerField::new('duration', new TranslatableMessage('Duration'));
        yield $this->addFieldSaga();
        yield $this->addFieldTags('movie');
        yield NumberField::new('evaluation', new TranslatableMessage('Evaluation'));
        yield IntegerField::new('votes', new TranslatableMessage('Votes'));
        yield TextField::new('trailer', new TranslatableMessage('Trailer'))->hideOnIndex();
        yield WysiwygField::new('citation', new TranslatableMessage('Citation'))->hideOnIndex();
        yield WysiwygField::new('description', new TranslatableMessage('Description'))->hideOnIndex();
        yield $this->addFieldCategories('movie');
        yield $this->addFieldImageUpload('img', $pageName);
        yield $this->addFieldBoolean('enable', new TranslatableMessage('Enable'));
        yield $this->addFieldBoolean('file', new TranslatableMessage('File'))->hideOnIndex();
        yield $this->addFieldBoolean('adult', new TranslatableMessage('Adult'));
        $date = $this->addTabDate();
        foreach ($date as $field) {
            yield $field;
        }
    }

    #[Override]
    public function configureFilters(Filters $filters): Filters
    {
        $this->addFilterEnable($filters);
        $filters->add('releaseDate');
        $filters->add('countries');

        $this->addFilterTags($filters, 'movie');
        $this->addFilterCategories($filters, 'movie');
        $this->addFilterSaga($filters);

        return $filters;
    }

    #[Override]
    public static function getEntityFqcn(): string
    {
        return Movie::class;
    }

    #[Route('/admin/movie/{entity}/imdb', name: 'admin_movie_imdb')]
    public function imdb(string $entity): RedirectResponse
    {
        $serviceEntityRepositoryLib = $this->getRepository();
        $movie                      = $serviceEntityRepositoryLib->find($entity);

        return $this->redirect('https://www.imdb.com/title/' . $movie->getImdb() . '/');
    }

    #[Route('/admin/movie/{entity}/tmdb', name: 'admin_movie_tmdb')]
    public function tmdb(string $entity): RedirectResponse
    {
        $serviceEntityRepositoryLib = $this->getRepository();
        $movie                      = $serviceEntityRepositoryLib->find($entity);

        return $this->redirect('https://www.themoviedb.org/movie/' . $movie->getTmdb());
    }

    #[Route('/admin/movie/{entity}/update', name: 'admin_movie_update')]
    public function update(string $entity): RedirectResponse
    {
        $serviceEntityRepositoryLib = $this->getRepository();
        $movie                      = $serviceEntityRepositoryLib->find($entity);
        $this->movieService->update($movie);
        $serviceEntityRepositoryLib->save($movie);

        return $this->redirectToRoute('admin_movie_index');
    }

    protected function addFieldSaga(): AssociationField
    {
        $associationField = AssociationField::new('saga', new TranslatableMessage('Saga'));
        $associationField->autocomplete();
        $associationField->setSortProperty('title');

        return $associationField;
    }

    protected function addFilterSaga(Filters $filters): void
    {
        $entityFilter = EntityFilter::new('saga', new TranslatableMessage('Sagas'));
        $filters->add($entityFilter);
    }

    private function configureActionsUpdateImage(): void
    {
        $request = $this->container->get('request_stack')->getCurrentRequest();
        $request->query->get('action', null);
    }

    private function setLinkImdbAction(): Action
    {
        $action = Action::new('imdb', new TranslatableMessage('IMDB Page'));
        $action->setHtmlAttributes(
            ['target' => '_blank']
        );
        $action->linkToUrl(
            fn (Movie $movie): string => $this->generateUrl(
                'admin_movie_imdb',
                [
                    'entity' => $movie->getId(),
                ]
            )
        );
        $action->displayIf(static fn ($entity): bool => is_null($entity->getDeletedAt()));

        return $action;
    }

    private function setLinkTmdbAction(): Action
    {
        $action = Action::new('tmdb', new TranslatableMessage('TMDB Page'));
        $action->setHtmlAttributes(
            ['target' => '_blank']
        );
        $action->linkToUrl(
            fn (Movie $movie): string => $this->generateUrl(
                'admin_movie_tmdb',
                [
                    'entity' => $movie->getId(),
                ]
            )
        );

        return $action;
    }

    private function setUpdateAction(): Action
    {
        $action = Action::new('update', new TranslatableMessage('Update'));
        $action->linkToUrl(
            fn (Movie $movie): string => $this->generateUrl(
                'admin_movie_update',
                [
                    'entity' => $movie->getId(),
                ]
            )
        );
        $action->displayIf(static fn ($entity): bool => is_null($entity->getDeletedAt()));

        return $action;
    }
}
