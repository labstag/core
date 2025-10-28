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
use EasyCorp\Bundle\EasyAdminBundle\Filter\ChoiceFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\EntityFilter;
use Labstag\Entity\Movie;
use Labstag\Field\WysiwygField;
use Labstag\Message\MovieMessage;
use Labstag\Repository\MovieRepository;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Intl\Countries;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Translation\TranslatableMessage;

class MovieCrudController extends CrudControllerAbstract
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
        $this->crudFieldFactory->setTabPrincipal();

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

        $episodeCollectionField = CollectionField::new('runtime', new TranslatableMessage('Runtime'));
        $episodeCollectionField->setTemplatePath('admin/field/runtime-movie.html.twig');
        $episodeCollectionField->hideOnForm();

        $integerField = IntegerField::new('duration', new TranslatableMessage('Duration'));
        $integerField->hideOnIndex();
        $integerField->hideOnDetail();

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
                $this->crudFieldFactory->idField(),
                $this->crudFieldFactory->booleanField('enable', (string) new TranslatableMessage('Enable')),
                $this->crudFieldFactory->titleField(),
                $this->crudFieldFactory->imageField('img', $pageName, self::getEntityFqcn()),
                $textField,
                $tmdbField,
                $certificationField,
                DateField::new('releaseDate', new TranslatableMessage('Release date')),
                $choiceField,
                $episodeCollectionField,
                $integerField,
                $this->addFieldSaga(),
                NumberField::new('evaluation', new TranslatableMessage('Evaluation')),
                IntegerField::new('votes', new TranslatableMessage('Votes')),
                $trailerField,
                $wysiwygField,
                $descriptionField,
                $this->crudFieldFactory->categoriesField('movie'),
                // image field déjà incluse dans baseIdentitySet
                $booleanField,
                $this->crudFieldFactory->booleanField('adult', (string) new TranslatableMessage('Adult')),
            ]
        );
        $this->crudFieldFactory->setTabDate($pageName);

        yield from $this->crudFieldFactory->getConfigureFields();
    }

    #[\Override]
    public function configureFilters(Filters $filters): Filters
    {
        $this->crudFieldFactory->addFilterEnable($filters);
        $movieRepository = $this->getMovieRepository();
        $certifications  = $movieRepository->getCertifications();

        $filters->add('releaseDate');
        $filters->add('countries');
        if ([] !== $certifications) {
            $filters->add(
                ChoiceFilter::new('certification', new TranslatableMessage('Certification'))->setChoices(
                    $certifications
                )
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
        $serviceEntityRepositoryAbstract = $this->getRepository();
        $movie                           = $serviceEntityRepositoryAbstract->find($entity);

        return $this->redirect('https://www.imdb.com/title/' . $movie->getImdb() . '/');
    }

    #[Route('/admin/movie/{entity}/tmdb', name: 'admin_movie_tmdb')]
    public function tmdb(string $entity): RedirectResponse
    {
        $serviceEntityRepositoryAbstract = $this->getRepository();
        $movie                           = $serviceEntityRepositoryAbstract->find($entity);

        return $this->redirect('https://www.themoviedb.org/movie/' . $movie->getTmdb());
    }

    #[Route('/admin/movie/{entity}/update', name: 'admin_movie_update')]
    public function update(string $entity, Request $request, MessageBusInterface $messageBus): RedirectResponse
    {
        $serviceEntityRepositoryAbstract = $this->getRepository();
        $movie                           = $serviceEntityRepositoryAbstract->find($entity);
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
        $serviceEntityRepositoryAbstract = $this->getRepository();
        assert($serviceEntityRepositoryAbstract instanceof MovieRepository);

        return $serviceEntityRepositoryAbstract;
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
