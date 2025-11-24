<?php

namespace Labstag\Controller\Admin;

use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Asset;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use Labstag\Api\TheMovieDbApi;
use Labstag\Entity\Movie;
use Labstag\Entity\Recommendation;
use Labstag\Entity\Saga;
use Labstag\Entity\Serie;
use Labstag\Message\MovieMessage;
use Labstag\Message\SerieMessage;
use Labstag\Repository\MovieRepository;
use Labstag\Repository\SerieRepository;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Translation\TranslatableMessage;

class RecommendationCrudController extends CrudControllerAbstract
{
    public function addToBdd(
        AdminContext $adminContext,
        TheMovieDbApi $theMovieDbApi,
        MovieRepository $movieRepository,
        SerieRepository $serieRepository,
        MessageBusInterface $messageBus,
    ): ?RedirectResponse
    {
        $request            = $adminContext->getRequest();
        $repositoryAbstract = $this->getRepository();
        $entity             = $repositoryAbstract->find($request->query->get('entityId'));
        if (!$entity instanceof Recommendation) {
            return $this->redirectToRoute('admin_recommendation_index');
        }

        $tmdbId   = $entity->getTmdb();
        $refserie = $entity->getRefserie();
        if ($refserie instanceof Serie) {
            return $this->addToBddSerie($serieRepository, $theMovieDbApi, $entity, $messageBus, $tmdbId);
        }

        $refmovie = $entity->getRefmovie();
        $refsaga  = $entity->getRefsaga();
        if ($refmovie instanceof Movie || $refsaga instanceof Saga) {
            return $this->addToBddMovie($movieRepository, $theMovieDbApi, $entity, $messageBus, $tmdbId);
        }

        return $this->redirectToRoute('admin_recommendation_index');
    }

    #[\Override]
    public function configureActions(Actions $actions): Actions
    {
        $this->actionsFactory->init($actions, self::getEntityFqcn(), static::class);
        $this->actionsFactory->setReadOnly(true);
        $this->setAddToBdd();
        $this->actionsFactory->disableDelete();

        return $this->actionsFactory->show();
    }

    #[\Override]
    public function configureCrud(Crud $crud): Crud
    {
        $crud = parent::configureCrud($crud);
        $crud->setEntityLabelInSingular(new TranslatableMessage('Recommendation'));
        $crud->setEntityLabelInPlural(new TranslatableMessage('Recommendations'));
        $crud->setDefaultSort(
            ['title' => 'ASC']
        );

        return $crud;
    }

    #[\Override]
    public function configureFields(string $pageName): iterable
    {
        $this->crudFieldFactory->setTabPrincipal($this->getContext());

        $textField = TextField::new('tmdb', new TranslatableMessage('Tmdb'));
        $textField->hideOnIndex();

        $imgField = TextField::new('poster', new TranslatableMessage('Poster'));
        $imgField->addJsFiles(
            Asset::fromEasyAdminAssetPackage('field-image.js'),
            Asset::fromEasyAdminAssetPackage('field-file-upload.js')
        );
        $imgField->setTemplatePath('admin/field/fieldurlimg.html.twig');

        $this->crudFieldFactory->addFieldsToTab(
            'principal',
            [
                $textField,
                $imgField,
                TextField::new('title', new TranslatableMessage('Title')),
                TextField::new('overview', new TranslatableMessage('Overview')),
                DateField::new('releaseDate', new TranslatableMessage('Release date')),
            ]
        );

        yield from $this->crudFieldFactory->getConfigureFields($pageName);
    }

    public static function getEntityFqcn(): string
    {
        return Recommendation::class;
    }

    public function setAddToBdd(): void
    {
        $action = Action::new('addToBdd', new TranslatableMessage('addToBdd'), 'fas fa-terminal');
        $action->displayAsLink();
        $action->linkToCrudAction('addToBdd');
        $action->setHtmlAttributes(
            ['target' => '_blank']
        );
        $this->actionsFactory->add(Crud::PAGE_INDEX, $action);
    }

    private function addToBddMovie(
        MovieRepository $movieRepository,
        TheMovieDbApi $theMovieDbApi,
        Recommendation $recommendation,
        MessageBusInterface $messageBus,
        string $tmdbId,
    ): RedirectResponse
    {
        $details = $theMovieDbApi->movies()->getDetails($tmdbId);
        if (0 === count($details)) {
            return $this->redirectToRoute('admin_recommendation_index');
        }

        $movie = $movieRepository->findOneBy(
            ['tmdb' => $tmdbId]
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

        $data = $theMovieDbApi->movies()->getMovieExternalIds($tmdbId);
        $movie = new Movie();
        $movie->setFile(false);
        $movie->setEnable(true);
        $movie->setAdult(false);
        $movie->setImdb($data['imdb_id'] ?? '');
        $movie->setTmdb($tmdbId);
        $movie->setTitle($recommendation->getTitle() ?? '');

        $movieRepository->save($movie);
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

    private function addToBddSerie(
        SerieRepository $serieRepository,
        TheMovieDbApi $theMovieDbApi,
        Recommendation $recommendation,
        MessageBusInterface $messageBus,
        string $tmdbId,
    ): RedirectResponse
    {
        $details = $theMovieDbApi->tvserie()->getDetails($tmdbId);
        if (0 === count($details)) {
            return $this->redirectToRoute('admin_recommendation_index');
        }

        $serie = $serieRepository->findOneBy(
            ['tmdb' => $tmdbId]
        );
        if ($serie instanceof Serie) {
            $this->addFlash(
                'warning',
                new TranslatableMessage(
                    'The %name% series is already present in the database',
                    [
                        '%name%' => $serie->getTitle(),
                    ]
                )
            );

            return $this->redirectToRoute(
                'admin_serie_detail',
                [
                    'entityId' => $serie->getId(),
                ]
            );
        }

        $data = $theMovieDbApi->tvserie()->getTvExternalIds($tmdbId);
        $serie = new Serie();
        $serie->setFile(false);
        $serie->setEnable(true);
        $serie->setAdult(false);
        $serie->setImdb($data['imdb_id'] ?? '');
        $serie->setTmdb($tmdbId);
        $serie->setTitle($recommendation->getTitle() ?? '');

        $serieRepository->save($serie);
        $messageBus->dispatch(new SerieMessage($serie->getId()));
        $this->addFlash(
            'success',
            new TranslatableMessage(
                'The %name% series has been added to the database',
                [
                    '%name%' => $serie->getTitle(),
                ]
            )
        );

        return $this->redirectToRoute(
            'admin_serie_detail',
            [
                'entityId' => $serie->getId(),
            ]
        );
    }
}
