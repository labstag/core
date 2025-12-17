<?php

namespace Labstag\Controller\Admin;

use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\CollectionField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\NumberField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Filter\EntityFilter;
use Labstag\Entity\Serie;
use Labstag\Field\WysiwygField;
use Labstag\Filter\CountriesFilter;
use Labstag\Form\Admin\SerieImportType;
use Labstag\Form\Admin\SerieType;
use Labstag\Message\ImportMessage;
use Labstag\Message\SerieAllMessage;
use Labstag\Message\SerieMessage;
use Override;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Intl\Countries;
use Symfony\Component\Translation\TranslatableMessage;

class SerieCrudController extends CrudControllerAbstract
{
    public function addByApi(Request $request): JsonResponse
    {
        $tmdbId       = $request->query->get('id');
        $serie        = $this->getRepository(Serie::class)->findOneBy(
            ['tmdb' => $tmdbId]
        );
        if ($serie instanceof Serie) {
            $message = new TranslatableMessage('Serie already exists');

            return new JsonResponse(
                [
                    'status'  => 'warning',
                    'id'      => $tmdbId,
                    'message' => $this->translator->trans($message->getMessage(), $message->getParameters()),
                ]
            );
        }

        $locale = $this->configurationService->getLocaleTmdb();
        $tmdb   = $this->theMovieDbApi->tvserie()->getDetails($tmdbId, $locale);
        if (is_null($tmdb)) {
            $message = new TranslatableMessage(
                'The series with the TMDB id %id% does not exist',
                ['%id%' => $tmdbId]
            );

            return new JsonResponse(
                [
                    'status'  => 'warning',
                    'id'      => $tmdbId,
                    'message' => $this->translator->trans($message->getMessage(), $message->getParameters()),
                ]
            );
        }

        $other  = $this->theMovieDbApi->tvserie()->getTvExternalIds($tmdbId);
        if (!isset($other['imdb_id'])) {
            $message = new TranslatableMessage('No Imdb id for this series');

            return new JsonResponse(
                [
                    'status'  => 'warning',
                    'id'      => $tmdbId,
                    'message' => $this->translator->trans($message->getMessage(), $message->getParameters()),
                ]
            );
        }

        $serie = new Serie();
        $serie->setFile(false);
        $serie->setEnable(true);
        $serie->setAdult(false);
        $serie->setTmdb($tmdbId);
        $serie->setImdb($other['imdb_id']);
        $serie->setTitle($tmdb['name']);

        $this->getRepository(Serie::class)->save($serie);
        $this->messageBus->dispatch(new SerieMessage($serie->getId()));
        $message = new TranslatableMessage('Serie is being added');

        return new JsonResponse(
            [
                'status'  => 'success',
                'id'      => $tmdbId,
                'message' => $this->translator->trans($message->getMessage(), $message->getParameters()),
            ]
        );
    }

    public function apiSerie(Request $request): Response
    {
        $page               = $request->query->get('page', 1);
        $all                = $request->request->all();
        $data               = [
            'imdb'  => $all['serie']['imdb'] ?? '',
            'title' => $all['serie']['title'] ?? '',
        ];
        $series = $this->serieService->getSerieApi($data, $page);

        return $this->render(
            'admin/api/serie/list.html.twig',
            [
                'page'       => $page,
                'controller' => self::class,
                'series'     => $series,
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
        $this->actionsFactory->setActionUpdateAll('updateAllSerie');
        $this->addActionNewSerie();
        $this->addActionImportSerie();

        return $this->actionsFactory->show();
    }

    #[Override]
    public function configureCrud(Crud $crud): Crud
    {
        $crud = parent::configureCrud($crud);
        $crud->setEntityLabelInSingular(new TranslatableMessage('Serie'));
        $crud->setEntityLabelInPlural(new TranslatableMessage('Series'));
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

        $tmdField = TextField::new('tmdb', new TranslatableMessage('Tmdb'));
        $tmdField->hideOnIndex();

        $certificationField = TextField::new('certification', new TranslatableMessage('Certification'));
        $certificationField->hideOnIndex();

        $choiceField = ChoiceField::new('countries', new TranslatableMessage('Countries'));
        $choiceField->setChoices(array_flip(Countries::getNames()));
        $choiceField->allowMultipleChoices();
        $choiceField->renderExpanded(false);

        $associationField = AssociationField::new('seasons', new TranslatableMessage('Seasons'));
        $associationField->setTemplatePath('admin/field/seasons.html.twig');
        $associationField->onlyOnDetail();

        $collectionField = CollectionField::new('runtime', new TranslatableMessage('Runtime'));
        $collectionField->setTemplatePath('admin/field/runtime-serie.html.twig');
        $collectionField->hideOnForm();
        $collectionField->hideOnIndex();

        $trailerField = TextField::new('trailer', new TranslatableMessage('Trailer'));
        $trailerField->hideOnIndex();

        $wysiwgTranslation = new TranslatableMessage('Citation');
        $wysiwygField      = WysiwygField::new('citation', $wysiwgTranslation->getMessage());
        $wysiwygField->hideOnIndex();

        $descriptionTranslation = new TranslatableMessage('Description');
        $descriptionField       = WysiwygField::new('description', $descriptionTranslation->getMessage());
        $descriptionField->hideOnIndex();

        $booleanField = $this->crudFieldFactory->booleanField('file', new TranslatableMessage('File'));
        $booleanField->hideOnIndex();

        $posterTranslation   = new TranslatableMessage('Poster');
        $backdropTranslation = new TranslatableMessage('Backdrop');

        $castingField = AssociationField::new('castings', new TranslatableMessage('Casting'));
        $castingField->setTemplatePath('admin/field/castings.html.twig');
        $castingField->onlyOnDetail();

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
                    $posterTranslation->getMessage()
                ),
                $this->crudFieldFactory->imageField(
                    'backdrop',
                    $pageName,
                    self::getEntityFqcn(),
                    $backdropTranslation->getMessage()
                ),
                $this->crudFieldFactory->booleanField('inProduction', new TranslatableMessage('in Production')),
                $textField,
                $tmdField,
                $certificationField,
                DateField::new('releaseDate', new TranslatableMessage('Release date')),
                DateField::new('lastreleaseDate', new TranslatableMessage('Last release date')),
                $choiceField,
                $collectionField,
                NumberField::new('evaluation', new TranslatableMessage('Evaluation'))->hideOnIndex(),
                IntegerField::new('votes', new TranslatableMessage('Votes'))->hideOnIndex(),
                $trailerField,
                $wysiwygField,
                $descriptionField,
                $this->crudFieldFactory->categoriesFieldForPage(self::getEntityFqcn(), $pageName),
                $this->crudFieldFactory->companiesFieldForPage(self::getEntityFqcn(), $pageName),
                $associationField,
                $booleanField,
                $castingField,
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

        $filters->add('releaseDate');
        $countries = $this->getRepository()->getCountries();
        if ([] != $countries) {
            $translatableMessage  = new TranslatableMessage('Countries');
            $countriesFilter      = CountriesFilter::new('countries', $translatableMessage->getMessage());
            $countriesFilter->setChoices(
                array_merge(
                    ['' => ''],
                    $countries
                )
            );
            $filters->add($countriesFilter);
        }

        $filters->add('inProduction');

        $this->crudFieldFactory->addFilterCategoriesFor($filters, self::getEntityFqcn());
        $this->addFilterCompanies($filters);

        return $filters;
    }

    public static function getEntityFqcn(): string
    {
        return Serie::class;
    }

    public function imdb(Request $request): RedirectResponse
    {
        $entityId                        = $request->query->get('entityId');
        $repositoryAbstract              = $this->getRepository();
        $serie                           = $repositoryAbstract->find($entityId);
        if (empty($serie->getImdb())) {
            return $this->redirectToRoute('admin_serie_index');
        }

        return $this->redirect('https://www.imdb.com/title/' . $serie->getImdb() . '/');
    }

    public function importFileSerie(Request $request): JsonResponse
    {
        $files   = $request->files->all();
        $file    = $files['serie_import']['file'] ?? null;
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
        $this->messageBus->dispatch(new ImportMessage($filename, 'serie', []));

        return new JsonResponse(
            [
                'status'  => 'success',
                'message' => 'Import started',
            ]
        );
    }

    public function jsonSerie(Request $request): JsonResponse
    {
        $entityId                        = $request->query->get('entityId');
        $repositoryAbstract              = $this->getRepository();
        $serie                           = $repositoryAbstract->find($entityId);
        $details                         = $this->theMovieDbApi->getDetailsSerie($serie);

        return new JsonResponse($details);
    }

    public function showModalImportSerie(Request $request): Response
    {
        $form    = $this->createForm(SerieImportType::class);
        $form->handleRequest($request);

        return $this->render(
            'admin/serie/import.html.twig',
            [
                'controller' => self::class,
                'form'       => $form->createView(),
            ]
        );
    }

    public function showModalSerie(Request $request): Response
    {
        $form    = $this->createForm(SerieType::class);
        $form->handleRequest($request);

        return $this->render(
            'admin/serie/new.html.twig',
            [
                'controller' => self::class,
                'form'       => $form->createView(),
            ]
        );
    }

    public function tmdb(Request $request): RedirectResponse
    {
        $entityId                        = $request->query->get('entityId');
        $repositoryAbstract              = $this->getRepository();
        $serie                           = $repositoryAbstract->find($entityId);

        return $this->redirect('https://www.themoviedb.org/tv/' . $serie->getTmdb());
    }

    public function updateAllSerie(): RedirectResponse
    {
        $this->messageBus->dispatch(new SerieAllMessage());

        return $this->redirectToRoute('admin_serie_index');
    }

    public function updateSerie(Request $request): RedirectResponse
    {
        $entityId                        = $request->query->get('entityId');
        $repositoryAbstract              = $this->getRepository();
        $serie                           = $repositoryAbstract->find($entityId);
        $this->messageBus->dispatch(new SerieMessage($serie->getId()));
        if ($request->headers->has('referer')) {
            $url = $request->headers->get('referer');
            if (is_string($url) && '' !== $url) {
                return $this->redirect($url);
            }
        }

        return $this->redirectToRoute('admin_serie_index');
    }

    protected function addFilterCompanies(Filters $filters): void
    {
        $entityFilter = EntityFilter::new('companies', new TranslatableMessage('Companies'));
        $filters->add($entityFilter);
    }

    private function addActionImportSerie(): void
    {
        if (!$this->actionsFactory->isTrash()) {
            return;
        }

        $action = Action::new('showModalImportSerie', new TranslatableMessage('Import'), 'fas fa-file-import');
        $action->linkToCrudAction('showModalImportSerie');
        $action->setHtmlAttributes(
            ['data-action' => 'show-modal']
        );
        $action->createAsGlobalAction();

        $this->actionsFactory->add(Crud::PAGE_INDEX, $action);
    }

    private function addActionNewSerie(): void
    {
        if (!$this->actionsFactory->isTrash()) {
            return;
        }

        $action = Action::new('showModalSerie', new TranslatableMessage('New serie'), 'fas fa-plus');
        $action->linkToCrudAction('showModalSerie');
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

        $action = Action::new('updateSerie', new TranslatableMessage('Update'), 'fas fa-sync-alt');
        $action->linkToCrudAction('updateSerie');
        $action->displayIf(static fn ($entity): bool => is_null($entity->getDeletedAt()));

        $this->actionsFactory->add(Crud::PAGE_DETAIL, $action);
        $this->actionsFactory->add(Crud::PAGE_EDIT, $action);
        $this->actionsFactory->add(Crud::PAGE_INDEX, $action);

        $action = Action::new('jsonSerie', new TranslatableMessage('Json'), 'fas fa-server');
        $action->linkToCrudAction('jsonSerie');
        $action->setHtmlAttributes(
            ['target' => '_blank']
        );
        $action->displayIf(static fn ($entity): bool => is_null($entity->getDeletedAt()));

        $this->actionsFactory->add(Crud::PAGE_DETAIL, $action);
        $this->actionsFactory->add(Crud::PAGE_EDIT, $action);
        $this->actionsFactory->add(Crud::PAGE_INDEX, $action);
    }
}
