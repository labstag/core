<?php

namespace Labstag\Controller\Admin;

use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateField;
use EasyCorp\Bundle\EasyAdminBundle\Field\FormField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\NumberField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Filter\ChoiceFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\EntityFilter;
use Labstag\Api\TheMovieDbApi;
use Labstag\Entity\Movie;
use Labstag\Field\WysiwygField;
use Labstag\Filter\CountriesFilter;
use Labstag\Message\MovieAllMessage;
use Labstag\Message\MovieMessage;
use Labstag\Service\FileService;
use Labstag\Service\JsonPaginatorService;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Intl\Countries;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Translation\TranslatableMessage;

class MovieCrudController extends CrudControllerAbstract
{
    public function addWithTmdb(
        AdminContext $adminContext,
        TheMovieDbApi $theMovieDbApi,
        MessageBusInterface $messageBus,
    ): Response
    {
        $tmdbId = $adminContext->getRequest()->query->get('tmdb');
        $name = $adminContext->getRequest()->query->get('name');
        $data = $theMovieDbApi->movies()->getMovieExternalIds($tmdbId);
        if (is_null($data)) {
            return $this->redirectToRoute('admin_movie_index');
        }

        $imdbId             = $data['imdb_id'];
        $repositoryAbstract = $this->getRepository();
        $movie              = $repositoryAbstract->findOneBy(
            ['imdb' => $imdbId]
        );
        if ($movie instanceof Movie) {
            $this->addFlash(
                'warning',
                new TranslatableMessage(
                    'The %name% movie is already present in the database',
                    [
                        '%name%' => $movie->getTitle(),
                    ]
                )
            );

            return $this->redirectToRoute(
                'admin_movie_detail',
                [
                    'entityId' => $movie->getId(),
                ]
            );
        }

        $movie = new Movie();
        $movie->setFile(false);
        $movie->setEnable(true);
        $movie->setAdult(false);
        $movie->setImdb($imdbId);
        $movie->setTitle($name);
        $movie->setTmdb($tmdbId);

        $repositoryAbstract->save($movie);
        $messageBus->dispatch(new MovieMessage($movie->getId()));
        $this->addFlash(
            'success',
            new TranslatableMessage(
                'The %name% movie has been added to the database',
                [
                    '%name%' => $movie->getTitle(),
                ]
            )
        );

        return $this->redirectToRoute(
            'admin_movie_detail',
            [
                'entityId' => $movie->getId(),
            ]
        );
    }

    #[\Override]
    public function configureActions(Actions $actions): Actions
    {
        $actions->add(Crud::PAGE_NEW, Action::SAVE_AND_CONTINUE);

        $this->actionsFactory->init($actions, self::getEntityFqcn(), static::class);
        $this->actionsFactory->setLinkImdbAction();
        $this->actionsFactory->setLinkTmdbAction();
        $this->setUpdateAction();
        $this->actionsFactory->setActionUpdateAll();
        $this->setShowAllRecommendations();

        return $this->actionsFactory->show();
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
        $this->crudFieldFactory->setTabPrincipal($this->getContext());

        $textField = TextField::new('imdb', new TranslatableMessage('Imdb'));
        $textField->hideOnIndex();

        $tmdbField = TextField::new('tmdb', new TranslatableMessage('Tmdb'));
        $tmdbField->hideOnIndex();

        $certificationField = TextField::new('certification', new TranslatableMessage('Certification'));
        $certificationField->hideOnIndex();

        $choiceField = ChoiceField::new('countries', new TranslatableMessage('Countries'));
        $choiceField->setChoices(array_flip(Countries::getNames()));
        $choiceField->allowMultipleChoices();
        $choiceField->renderExpanded(false);

        $integerField = IntegerField::new('duration', new TranslatableMessage('Duration'));
        $integerField->setTemplatePath('admin/field/runtime-movie.html.twig');

        $trailerField = TextField::new('trailer', new TranslatableMessage('Trailer'));
        $trailerField->hideOnIndex();

        $wysiwygField = WysiwygField::new('citation', new TranslatableMessage('Citation'));
        $wysiwygField->hideOnIndex();

        $descriptionField = WysiwygField::new('description', new TranslatableMessage('Description'));
        $descriptionField->hideOnIndex();

        $booleanField = $this->crudFieldFactory->booleanField('file', (string) new TranslatableMessage('File'));
        $booleanField->hideOnIndex();

        $this->crudFieldFactory->addFieldsToTab(
            'principal',
            [
                $this->crudFieldFactory->slugField(),
                $this->crudFieldFactory->booleanField('enable', (string) new TranslatableMessage('Enable')),
                $this->crudFieldFactory->titleField(),
                $this->crudFieldFactory->imageField(
                    'poster',
                    $pageName,
                    self::getEntityFqcn(),
                    new TranslatableMessage('Poster')
                ),
                $this->crudFieldFactory->imageField(
                    'backdrop',
                    $pageName,
                    self::getEntityFqcn(),
                    new TranslatableMessage('Backdrop')
                ),
                $textField,
                $tmdbField,
                $certificationField,
                DateField::new('releaseDate', new TranslatableMessage('Release date')),
                $choiceField,
                $integerField,
                $this->addFieldSaga(),
                NumberField::new('evaluation', new TranslatableMessage('Evaluation'))->hideOnIndex(),
                IntegerField::new('votes', new TranslatableMessage('Votes'))->hideOnIndex(),
                $trailerField,
                $wysiwygField,
                $descriptionField,
                $this->crudFieldFactory->categoriesFieldForPage(self::getEntityFqcn(), $pageName),
                $this->crudFieldFactory->companiesFieldForPage(self::getEntityFqcn(), $pageName),
                // image field déjà incluse dans baseIdentitySet
                $booleanField,
                $this->crudFieldFactory->booleanField('adult', (string) new TranslatableMessage('Adult')),
            ]
        );
        $this->crudFieldFactory->setTabDate($pageName);
        $this->addRecommendationTab($pageName);

        yield from $this->crudFieldFactory->getConfigureFields($pageName);
    }

    #[\Override]
    public function configureFilters(Filters $filters): Filters
    {
        $this->crudFieldFactory->addFilterEnable($filters);
        $repositoryAbstract = $this->getRepository();
        $certifications     = $repositoryAbstract->getCertifications();
        $filters->add('releaseDate');
        $countries = $repositoryAbstract->getCountries();
        if ([] != $countries) {
            $countriesFilter = CountriesFilter::new('countries', new TranslatableMessage('Countries'));
            $countriesFilter->setChoices(
                array_merge(
                    ['' => ''],
                    $countries
                )
            );
            $filters->add($countriesFilter);
        }

        if ([] !== $certifications) {
            $certificationFilter = ChoiceFilter::new('certification', new TranslatableMessage('Certification'));
            $certificationFilter->setChoices(
                array_merge(
                    ['' => ''],
                    $certifications
                )
            );
            $filters->add($certificationFilter);
        }

        $this->crudFieldFactory->addFilterCategoriesFor($filters, self::getEntityFqcn());
        $this->addFilterSaga($filters);
        $this->addFilterCompanies($filters);

        return $filters;
    }

    public static function getEntityFqcn(): string
    {
        return Movie::class;
    }

    public function imdb(AdminContext $adminContext): RedirectResponse
    {
        $entityId = $adminContext->getRequest()->query->get('entityId');
        $repositoryAbstract              = $this->getRepository();
        $movie                           = $repositoryAbstract->find($entityId);

        return $this->redirect('https://www.imdb.com/title/' . $movie->getImdb() . '/');
    }

    public function jsonMovie(AdminContext $adminContext, TheMovieDbApi $theMovieDbApi): JsonResponse
    {
        $entityId = $adminContext->getRequest()->query->get('entityId');
        $repositoryAbstract              = $this->getRepository();
        $movie                           = $repositoryAbstract->find($entityId);

        $details = $theMovieDbApi->getDetailsMovie($movie);

        return new JsonResponse($details);
    }

    public function recommendationsAll(
        FileService $fileService,
        JsonPaginatorService $jsonPaginatorService,
    ): Response
    {
        $file         = $fileService->getFileInAdapter('private', 'recommendations-movie.json');
        if (!is_file($file)) {
            return $this->redirectToRoute('admin_serie_index');
        }

        $pagination = $jsonPaginatorService->paginate($file, 'title');

        return $this->render(
            'admin/movie/recommendations.html.twig',
            ['pagination' => $pagination]
        );
    }

    public function setShowAllRecommendations(): void
    {
        if (!$this->actionsFactory->isTrash()) {
            return;
        }

        $action = Action::new('recommendationsAll', new TranslatableMessage('all recommendations'), 'fas fa-terminal');
        $action->displayAsLink();
        $action->linkToCrudAction('recommendationsAll');
        $action->createAsGlobalAction();

        $this->actionsFactory->add(Crud::PAGE_INDEX, $action);
    }

    public function tmdb(AdminContext $adminContext): RedirectResponse
    {
        $entityId = $adminContext->getRequest()->query->get('entityId');
        $repositoryAbstract              = $this->getRepository();
        $movie                           = $repositoryAbstract->find($entityId);

        return $this->redirect('https://www.themoviedb.org/movie/' . $movie->getTmdb());
    }

    public function updateAll(MessageBusInterface $messageBus): RedirectResponse
    {
        $messageBus->dispatch(new MovieAllMessage());

        return $this->redirectToRoute('admin_movie_index');
    }

    public function updateMovie(
        AdminContext $adminContext,
        Request $request,
        MessageBusInterface $messageBus,
    ): RedirectResponse
    {
        $entityId = $adminContext->getRequest()->query->get('entityId');
        $repositoryAbstract              = $this->getRepository();
        $movie                           = $repositoryAbstract->find($entityId);
        $messageBus->dispatch(new MovieMessage($movie->getId()));
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

    protected function addFilterCompanies(Filters $filters): void
    {
        $entityFilter = EntityFilter::new('companies', new TranslatableMessage('Companies'));
        $filters->add($entityFilter);
    }

    protected function addFilterSaga(Filters $filters): void
    {
        $entityFilter = EntityFilter::new('saga', new TranslatableMessage('Sagas'));
        $filters->add($entityFilter);
    }

    private function addRecommendationTab(string $pageName): void
    {
        if (Crud::PAGE_DETAIL !== $pageName) {
            return;
        }

        $entity = $this->getContext()->getEntity()->getInstance();
        $recommendations = $this->movieService->recommendations($entity);
        if ([] === $recommendations) {
            return;
        }

        $this->crudFieldFactory->addTab(
            'recommendations',
            FormField::addTab(new TranslatableMessage('Recommendations'))
        );

        $textField = TextField::new('id', new TranslatableMessage('Recommendations'));
        $textField->setTemplatePath('admin/field/recommendations.html.twig');

        $this->crudFieldFactory->addFieldsToTab('recommendations', [$textField]);
    }

    private function setUpdateAction(): void
    {
        if (!$this->actionsFactory->isTrash()) {
            return;
        }

        $action = Action::new('updateMovie', new TranslatableMessage('Update'));
        $action->linkToCrudAction('updateMovie');
        $action->displayIf(static fn ($entity): bool => is_null($entity->getDeletedAt()));

        $this->actionsFactory->add(Crud::PAGE_DETAIL, $action);
        $this->actionsFactory->add(Crud::PAGE_EDIT, $action);
        $this->actionsFactory->add(Crud::PAGE_INDEX, $action);

        $action = Action::new('jsonMovie', new TranslatableMessage('Json'));
        $action->linkToCrudAction('jsonMovie');
        $action->setHtmlAttributes(
            ['target' => '_blank']
        );
        $action->displayIf(static fn ($entity): bool => is_null($entity->getDeletedAt()));

        $this->actionsFactory->add(Crud::PAGE_DETAIL, $action);
        $this->actionsFactory->add(Crud::PAGE_EDIT, $action);
        $this->actionsFactory->add(Crud::PAGE_INDEX, $action);
    }
}
