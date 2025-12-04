<?php

namespace Labstag\Controller\Admin;

use Labstag\Service\EmailService;
use Labstag\Service\Imdb\SerieService;
use Labstag\Service\FormService;
use Labstag\Service\SiteService;
use Labstag\Service\SlugService;
use Labstag\Service\Imdb\SeasonService;
use Labstag\Service\SecurityService;
use Labstag\Service\BlockService;
use Labstag\Service\Imdb\EpisodeService;
use Labstag\Service\Imdb\SagaService;
use Labstag\Service\ParagraphService;
use Labstag\Service\WorkflowService;
use Symfony\Component\HttpFoundation\RequestStack;
use Labstag\Service\UserService;
use Labstag\Controller\Admin\Factory\ActionsFactory;
use Labstag\Controller\Admin\Factory\CrudFieldFactory;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use Doctrine\Persistence\ManagerRegistry;
use Override;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\NumberField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Filter\ChoiceFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\EntityFilter;
use Labstag\Api\TheMovieDbApi;
use Labstag\Entity\Movie;
use Labstag\Field\WysiwygField;
use Labstag\Filter\CountriesFilter;
use Labstag\Form\Admin\MovieImportType;
use Labstag\Form\Admin\MovieType;
use Labstag\Message\ImportMessage;
use Labstag\Message\MovieAllMessage;
use Labstag\Message\MovieMessage;
use Labstag\Repository\MovieRepository;
use Labstag\Service\ConfigurationService;
use Labstag\Service\FileService;
use Labstag\Service\Imdb\MovieService;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Intl\Countries;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Translation\TranslatableMessage;
use Symfony\Contracts\Translation\TranslatorInterface;

class MovieCrudController extends CrudControllerAbstract
{

    public function addByApi(Request $request): JsonResponse
    {
        $tmdbId       = $request->query->get('id');
        $movie        = $this->getRepository(Movie::class)->findOneBy(
            ['tmdb' => $tmdbId]
        );
        if ($movie instanceof Movie) {
            return new JsonResponse(
                [
                    'status'  => 'warning',
                    'id'      => $tmdbId,
                    'message' => $this->translator->trans(new TranslatableMessage('Movie already exists')),
                ]
            );
        }

        $locale = $this->configurationService->getLocaleTmdb();
        $tmdb   = $this->theMovieDbApi->movies()->getDetails($tmdbId, $locale);
        if (is_null($tmdb)) {
            return new JsonResponse(
                [
                    'status'  => 'error',
                    'id'      => $tmdbId,
                    'message' => $this->translator->trans(
                        new TranslatableMessage(
                            'The movie with the TMDB id %id% does not exist',
                            ['%id%' => $tmdbId]
                        )
                    ),
                ]
            );
        }

        $other  = $this->theMovieDbApi->movies()->getMovieExternalIds($tmdbId);
        if (!isset($other['imdb_id'])) {
            return new JsonResponse(
                [
                    'status'  => 'warning',
                    'id'      => $tmdbId,
                    'message' => $this->translator->trans(new TranslatableMessage('No Imdb id for this movie')),
                ]
            );
        }

        $movie = new Movie();
        $movie->setEnable(true);
        $movie->setAdult(false);
        $movie->setFile(false);
        $movie->setTmdb($tmdbId);
        $movie->setImdb($other['imdb_id']);
        $movie->setTitle($tmdb['title']);

        $this->getRepository(Movie::class)->save($movie);
        $this->messageBus->dispatch(new MovieMessage($movie->getId()));
        return new JsonResponse(
            [
                'status'  => 'success',
                'id'      => $tmdbId,
                'message' => $this->translator->trans(new TranslatableMessage('Movie is being added')),
            ]
        );
    }

    public function apiMovie(Request $request): Response
    {
        $page               = $request->query->get('page', 1);
        $all                = $request->request->all();
        $data = [
            'imdb'  => $all['movie']['imdb'] ?? '',
            'title' => $all['movie']['title'] ?? '',
        ];
        $movies = $this->movieService->getMovieApi($data, $page);
        return $this->render(
            'admin/api/movie/list.html.twig',
            [
                'page'       => $page,
                'controller' => self::class,
                'movies'     => $movies,
            ]
        );
    }

    #[Override]
    public function configureActions(Actions $actions): Actions
    {
        $this->actionsFactory->init($actions, self::getEntityFqcn(), static::class);
        $this->actionsFactory->remove(Crud::PAGE_INDEX, Action::NEW);
        $this->actionsFactory->setLinkImdbAction();
        $this->actionsFactory->setLinkTmdbAction();
        $this->setUpdateAction();
        $this->actionsFactory->setActionUpdateAll('updateAllMovie');
        $this->addActionNewMovie();
        $this->addActionImportMovie();

        return $this->actionsFactory->show();
    }

    #[Override]
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

    #[Override]
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

        $booleanField = $this->crudFieldFactory->booleanField('file', new TranslatableMessage('File'));
        $booleanField->hideOnIndex();

        $this->crudFieldFactory->addFieldsToTab(
            'principal',
            [
                $this->crudFieldFactory->slugField(),
                $this->crudFieldFactory->booleanField('enable', new TranslatableMessage('Enable')),
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
                $this->crudFieldFactory->booleanField('adult', new TranslatableMessage('Adult')),
            ]
        );
        $this->crudFieldFactory->setTabDate($pageName);

        yield from $this->crudFieldFactory->getConfigureFields($pageName);
    }

    #[Override]
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

    public function imdb(Request $request): RedirectResponse
    {
        $entityId = $request->query->get('entityId');
        $repositoryAbstract              = $this->getRepository();
        $movie                           = $repositoryAbstract->find($entityId);
        return $this->redirect('https://www.imdb.com/title/' . $movie->getImdb() . '/');
    }

    public function importFile(Request $request): JsonResponse
    {
        $files   = $request->files->all();
        $file    = $files['movie_import']['file'] ?? null;
        if (null === $file) {
            return new JsonResponse(
                [
                    'status'  => 'error',
                    'message' => 'No file uploaded',
                ]
            );
        }

        $content   = file_get_contents($file->getPathname());
        $extension = $file->getClientOriginalExtension();
        $filename  = uniqid('import_', true) . '.' . $extension;
        $this->fileService->saveFileInAdapter('private', $filename, $content);
        $this->messageBus->dispatch(new ImportMessage($filename, 'movie', []));
        return new JsonResponse(
            [
                'status'  => 'success',
                'message' => 'Import started',
            ]
        );
    }

    public function jsonMovie(Request $request): JsonResponse
    {
        $entityId = $request->query->get('entityId');
        $repositoryAbstract              = $this->getRepository();
        $movie                           = $repositoryAbstract->find($entityId);
        $details = $this->theMovieDbApi->getDetailsMovie($movie);
        return new JsonResponse($details);
    }

    public function showModalImportMovie(Request $request): Response
    {
        $form    = $this->createForm(MovieImportType::class);
        $form->handleRequest($request);
        return $this->render(
            'admin/movie/import.html.twig',
            [
                'controller' => self::class,
                'form'       => $form->createView(),
            ]
        );
    }

    public function showModalMovie(Request $request): Response
    {
        $form    = $this->createForm(MovieType::class);
        $form->handleRequest($request);
        return $this->render(
            'admin/movie/new.html.twig',
            [
                'controller' => self::class,
                'form'       => $form->createView(),
            ]
        );
    }

    public function tmdb(Request $request): RedirectResponse
    {
        $entityId = $request->query->get('entityId');
        $repositoryAbstract              = $this->getRepository();
        $movie                           = $repositoryAbstract->find($entityId);
        return $this->redirect('https://www.themoviedb.org/movie/' . $movie->getTmdb());
    }

    public function updateAllMovie(): RedirectResponse
    {
        $this->messageBus->dispatch(new MovieAllMessage());
        return $this->redirectToRoute('admin_movie_index');
    }

    public function updateMovie(
        Request $request,
    ): RedirectResponse
    {
        $entityId = $request->query->get('entityId');
        $repositoryAbstract              = $this->getRepository();
        $movie                           = $repositoryAbstract->find($entityId);
        $this->messageBus->dispatch(new MovieMessage($movie->getId()));
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

    private function addActionImportMovie(): void
    {
        if (!$this->actionsFactory->isTrash()) {
            return;
        }

        $action = Action::new('showModalImportMovie', new TranslatableMessage('Import'), 'fas fa-file-import');
        $action->linkToCrudAction('showModalImportMovie');
        $action->setHtmlAttributes(
            ['data-action' => 'show-modal']
        );
        $action->createAsGlobalAction();

        $this->actionsFactory->add(Crud::PAGE_INDEX, $action);
    }

    private function addActionNewMovie(): void
    {
        if (!$this->actionsFactory->isTrash()) {
            return;
        }

        $action = Action::new('showModalMovie', new TranslatableMessage('New movie'), 'fas fa-plus-circle');
        $action->linkToCrudAction('showModalMovie');
        $action->setHtmlAttributes(
            ['data-action' => 'show-modal']
        );
        $action->createAsGlobalAction();

        $this->actionsFactory->add(Crud::PAGE_INDEX, $action);
    }

    private function setUpdateAction(): void
    {
        if (!$this->actionsFactory->isTrash()) {
            return;
        }

        $action = Action::new('updateMovie', new TranslatableMessage('Update'), 'fas fa-sync-alt');
        $action->linkToCrudAction('updateMovie');
        $action->displayIf(static fn ($entity): bool => is_null($entity->getDeletedAt()));

        $this->actionsFactory->add(Crud::PAGE_DETAIL, $action);
        $this->actionsFactory->add(Crud::PAGE_EDIT, $action);
        $this->actionsFactory->add(Crud::PAGE_INDEX, $action);

        $action = Action::new('jsonMovie', new TranslatableMessage('Json'), 'fas fa-server');
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
