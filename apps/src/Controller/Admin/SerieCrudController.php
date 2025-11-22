<?php

namespace Labstag\Controller\Admin;

use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\CollectionField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\NumberField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Filter\EntityFilter;
use Labstag\Api\TheMovieDbApi;
use Labstag\Entity\Serie;
use Labstag\Field\WysiwygField;
use Labstag\Filter\CountriesFilter;
use Labstag\Message\SerieAllMessage;
use Labstag\Message\SerieMessage;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Intl\Countries;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Translation\TranslatableMessage;

class SerieCrudController extends CrudControllerAbstract
{
    #[\Override]
    public function configureActions(Actions $actions): Actions
    {
        $this->actionsFactory->init($actions, self::getEntityFqcn(), static::class);
        $this->actionsFactory->setLinkImdbAction();
        $this->actionsFactory->setLinkTmdbAction();
        $this->setUpdateAction();
        $this->actionsFactory->setActionUpdateAll();

        return $this->actionsFactory->show();
    }

    #[\Override]
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

    #[\Override]
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
                $this->crudFieldFactory->booleanField(
                    'inProduction',
                    (string) new TranslatableMessage('in Production')
                ),
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
                $this->crudFieldFactory->booleanField('adult', (string) new TranslatableMessage('Adult')),
            ]
        );
        $this->crudFieldFactory->setTabDate($pageName);

        yield from $this->crudFieldFactory->getConfigureFields($pageName);
    }

    #[\Override]
    public function configureFilters(Filters $filters): Filters
    {
        $this->crudFieldFactory->addFilterEnable($filters);

        $filters->add('releaseDate');
        $countries = $this->getRepository()->getCountries();
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

        $filters->add('inProduction');

        $this->crudFieldFactory->addFilterCategoriesFor($filters, self::getEntityFqcn());
        $this->addFilterCompanies($filters);

        return $filters;
    }

    public static function getEntityFqcn(): string
    {
        return Serie::class;
    }

    public function imdb(AdminContext $adminContext): RedirectResponse
    {
        $entityId = $adminContext->getRequest()->query->get('entityId');
        $repositoryAbstract              = $this->getRepository();
        $serie                           = $repositoryAbstract->find($entityId);
        if (empty($serie->getImdb())) {
            return $this->redirectToRoute('admin_serie_index');
        }

        return $this->redirect('https://www.imdb.com/title/' . $serie->getImdb() . '/');
    }

    public function jsonSerie(AdminContext $adminContext, TheMovieDbApi $theMovieDbApi): JsonResponse
    {
        $entityId = $adminContext->getRequest()->query->get('entityId');
        $repositoryAbstract              = $this->getRepository();
        $serie                           = $repositoryAbstract->find($entityId);

        $details = $theMovieDbApi->getDetailsSerie($serie);

        return new JsonResponse($details);
    }

    public function tmdb(AdminContext $adminContext): RedirectResponse
    {
        $entityId = $adminContext->getRequest()->query->get('entityId');
        $repositoryAbstract              = $this->getRepository();
        $serie                           = $repositoryAbstract->find($entityId);

        return $this->redirect('https://www.themoviedb.org/tv/' . $serie->getTmdb());
    }

    public function updateAll(MessageBusInterface $messageBus): RedirectResponse
    {
        $messageBus->dispatch(new SerieAllMessage());

        return $this->redirectToRoute('admin_serie_index');
    }

    public function updateSerie(
        AdminContext $adminContext,
        Request $request,
        MessageBusInterface $messageBus,
    ): RedirectResponse
    {
        $entityId = $adminContext->getRequest()->query->get('entityId');
        $repositoryAbstract              = $this->getRepository();
        $serie                           = $repositoryAbstract->find($entityId);
        $messageBus->dispatch(new SerieMessage($serie->getId()));
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

    private function setUpdateAction(): void
    {
        if (!$this->actionsFactory->isTrash()) {
            return;
        }

        $action = Action::new('updateSerie', new TranslatableMessage('Update'));
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
