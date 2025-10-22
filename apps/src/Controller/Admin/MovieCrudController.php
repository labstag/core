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
use EasyCorp\Bundle\EasyAdminBundle\Filter\ChoiceFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\EntityFilter;
use Labstag\Controller\Admin\Abstract\AbstractCrudControllerLib;
use Labstag\Entity\Movie;
use Labstag\Field\WysiwygField;
use Labstag\Repository\MovieRepository;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Intl\Countries;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Translation\TranslatableMessage;

class MovieCrudController extends AbstractCrudControllerLib
{
    #[\Override]
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

    #[\Override]
    public function configureCrud(Crud $crud): Crud
    {
        $crud = parent::configureCrud($crud);
        $crud->setEntityLabelInSingular(new TranslatableMessage('Movie'));
        $crud->setEntityLabelInPlural(new TranslatableMessage('Movies'));
        $crud->setDefaultSort(
            ['title' => 'ASC']
        );

        return $crud;
    }

    #[\Override]
    public function configureFields(string $pageName): iterable
    {
        yield $this->addTabPrincipal();
        foreach ($this->crudFieldFactory->baseIdentitySet(
            $pageName,
            self::getEntityFqcn(),
            withSlug: false
        ) as $field) {
            yield $field;
        }

        yield TextField::new('imdb', new TranslatableMessage('Imdb'))->hideOnIndex();
        yield TextField::new('tmdb', new TranslatableMessage('Tmdb'))->hideOnIndex();
        yield TextField::new('certification', new TranslatableMessage('Certification'))->hideOnIndex();
        yield DateField::new('releaseDate', new TranslatableMessage('Release date'));
        $choiceField = ChoiceField::new('countries', new TranslatableMessage('Countries'));
        $choiceField->setChoices(array_flip(Countries::getNames()));
        $choiceField->allowMultipleChoices();
        $choiceField->renderExpanded(false);
        yield $choiceField;
        yield from [
            IntegerField::new('duration', new TranslatableMessage('Duration')),
            $this->addFieldSaga(),
            NumberField::new('evaluation', new TranslatableMessage('Evaluation')),
            IntegerField::new('votes', new TranslatableMessage('Votes')),
            TextField::new('trailer', new TranslatableMessage('Trailer'))->hideOnIndex(),
            WysiwygField::new('citation', new TranslatableMessage('Citation'))->hideOnIndex(),
            WysiwygField::new('description', new TranslatableMessage('Description'))->hideOnIndex(),
            $this->crudFieldFactory->categoriesField('movie'),
            // image field déjà incluse dans baseIdentitySet
            $this->crudFieldFactory->booleanField('file', (string) new TranslatableMessage('File'))->hideOnIndex(),
            $this->crudFieldFactory->booleanField('adult', (string) new TranslatableMessage('Adult')),
        ];
        foreach ($this->crudFieldFactory->dateSet($pageName) as $field) {
            yield $field;
        }
    }

    #[\Override]
    public function configureFilters(Filters $filters): Filters
    {
        $this->crudFieldFactory->addFilterEnable($filters);
        $movieRepository = $this->getMovieRepository();
        $certifications  = $movieRepository->getCertifications();

        $filters->add('releaseDate');
        $filters->add('countries');
        if (count($certifications) > 0) {
            $filters->add(
                ChoiceFilter::new('certification', new TranslatableMessage('Certification'))->setChoices($certifications)
            );
        }

        $this->crudFieldFactory->addFilterTags($filters, 'movie');
        $this->crudFieldFactory->addFilterCategories($filters, 'movie');
        $this->addFilterSaga($filters);

        return $filters;
    }

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
    public function update(string $entity, Request $request): RedirectResponse
    {
        $serviceEntityRepositoryLib = $this->getRepository();
        $movie                      = $serviceEntityRepositoryLib->find($entity);
        $this->movieService->update($movie);
        $serviceEntityRepositoryLib->save($movie);
        if ($request->headers->has('referer')) {
            $url = $request->headers->get('referer');
            if (is_string($url) && '' !== $url) {
                return $this->redirect($url);
            }
        }

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

    /**
     * Get the MovieRepository with proper typing for PHPStan.
     */
    private function getMovieRepository(): MovieRepository
    {
        $serviceEntityRepositoryLib = $this->getRepository();
        assert($serviceEntityRepositoryLib instanceof MovieRepository);

        return $serviceEntityRepositoryLib;
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
