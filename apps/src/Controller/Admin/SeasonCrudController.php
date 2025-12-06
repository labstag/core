<?php

namespace Labstag\Controller\Admin;

use Doctrine\Persistence\Mapping\ClassMetadata;
use Doctrine\Persistence\ObjectManager;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\CollectionField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Filter\EntityFilter;
use Labstag\Entity\Season;
use Labstag\Field\WysiwygField;
use Labstag\Message\SeasonAllMessage;
use Labstag\Message\SeasonMessage;
use Override;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Translation\TranslatableMessage;

class SeasonCrudController extends CrudControllerAbstract
{
    #[Override]
    public function configureActions(Actions $actions): Actions
    {
        $this->actionsFactory->init($actions, self::getEntityFqcn(), static::class);
        $this->setUpdateAction();
        $this->actionsFactory->setLinkTmdbAction();
        $this->actionsFactory->setActionUpdateAll('updateAllSeason');

        return $this->actionsFactory->show();
    }

    #[Override]
    public function configureCrud(Crud $crud): Crud
    {
        $crud = parent::configureCrud($crud);
        $crud->setEntityLabelInSingular(new TranslatableMessage('Season'));
        $crud->setEntityLabelInPlural(new TranslatableMessage('Seasons'));
        $crud->setDefaultSort(
            ['number' => 'ASC']
        );

        return $crud;
    }

    #[Override]
    public function configureFields(string $pageName): iterable
    {
        $this->crudFieldFactory->setTabPrincipal($this->getContext());
        $textField = TextField::new('tmdb', new TranslatableMessage('Tmdb'));
        $textField->hideOnIndex();

        $collectionField = CollectionField::new('runtime', new TranslatableMessage('Runtime'));
        $collectionField->setTemplatePath('admin/field/runtime-season.html.twig');
        $collectionField->hideOnForm();

        $wysiwygField = WysiwygField::new('overview', new TranslatableMessage('Overview'));
        $wysiwygField->hideOnIndex();

        $this->crudFieldFactory->addFieldsToTab(
            'principal',
            [
                $this->crudFieldFactory->booleanField('enable', new TranslatableMessage('Enable')),
                $this->crudFieldFactory->slugField(),
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
                AssociationField::new('refserie', new TranslatableMessage('Serie')),
                IntegerField::new('number', new TranslatableMessage('Number')),
                DateField::new('airDate', new TranslatableMessage('Air date')),
                $this->episodesFieldForPage(self::getEntityFqcn(), $pageName),
                $collectionField,
                $wysiwygField,
            ]
        );
        $this->crudFieldFactory->setTabDate($pageName);

        yield from $this->crudFieldFactory->getConfigureFields($pageName);
    }

    #[Override]
    public function configureFilters(Filters $filters): Filters
    {
        $this->crudFieldFactory->addFilterEnable($filters);
        $filters->add(EntityFilter::new('refserie', new TranslatableMessage('Serie')));

        return $filters;
    }

    public function episodesField(): AssociationField
    {
        $associationField = AssociationField::new('episodes', new TranslatableMessage('Episodes'));
        $associationField->setTemplatePath('admin/field/episodes.html.twig');

        return $associationField;
    }

    /**
     * Page-aware variant to avoid AssociationConfigurator errors on index/detail pages.
     * - On index/detail: always return a read-only CollectionField (count/list via template).
     * - On edit/new: only return an AssociationField if Doctrine metadata confirms the association,
     *   otherwise hide the field on forms (no-op for safety).
     */
    public function episodesFieldForPage(string $entityFqcn, string $pageName): AssociationField|CollectionField
    {
        $associationField = $this->episodesField();
        // Always safe on listing/detail pages: no AssociationField to configure
        if (in_array($pageName, [Crud::PAGE_INDEX, Crud::PAGE_DETAIL], true)
        ) {
            $associationField->hideOnForm();

            return $associationField;
        }

        // For edit/new pages, check the real Doctrine association
        $entityManager       = $this->managerRegistry->getManagerForClass($entityFqcn);
        $metadata            = $entityManager instanceof ObjectManager ? $entityManager->getClassMetadata(
            $entityFqcn
        ) : null;

        if ($metadata instanceof ClassMetadata && $metadata->hasAssociation('episodes')) {
            $associationField->autocomplete();
            $associationField->setFormTypeOption('by_reference', false);

            return $associationField;
        }

        // No association: ensure nothing is rendered on the form
        $associationField->hideOnForm();

        return $associationField;
    }

    public static function getEntityFqcn(): string
    {
        return Season::class;
    }

    public function jsonSeason(Request $request): JsonResponse
    {
        $entityId                         = $request->query->get('entityId');
        $repositoryAbstract               = $this->getRepository();
        $season                           = $repositoryAbstract->find($entityId);
        $details                          = $this->theMovieDbApi->getDetailsSeason($season);

        return new JsonResponse($details);
    }

    public function tmdb(Request $request): RedirectResponse
    {
        $entityId                         = $request->query->get('entityId');
        $repositoryAbstract               = $this->getRepository();
        $season                           = $repositoryAbstract->find($entityId);

        return $this->redirect(
            'https://www.themoviedb.org/tv/' . $season->getRefserie()->getTmdb() . '/season/' . $season->getNumber()
        );
    }

    public function updateAllSeason(): RedirectResponse
    {
        $this->messageBus->dispatch(new SeasonAllMessage());

        return $this->redirectToRoute('admin_season_index');
    }

    public function updateSeason(Request $request): RedirectResponse
    {
        $entityId                         = $request->query->get('entityId');
        $repositoryAbstract               = $this->getRepository();
        $season                           = $repositoryAbstract->find($entityId);
        $this->messageBus->dispatch(new SeasonMessage($season->getId()));
        if ($request->headers->has('referer')) {
            $url = $request->headers->get('referer');
            if (is_string($url) && '' !== $url) {
                return $this->redirect($url);
            }
        }

        return $this->redirectToRoute('admin_season_index');
    }

    private function setUpdateAction(): void
    {
        if (!$this->actionsFactory->isTrash()) {
            return;
        }

        $action = Action::new('updateSeason', new TranslatableMessage('Update'), 'fas fa-sync-alt');
        $action->linkToCrudAction('updateSeason');
        $action->displayIf(static fn ($entity): bool => is_null($entity->getDeletedAt()));

        $this->actionsFactory->add(Crud::PAGE_DETAIL, $action);
        $this->actionsFactory->add(Crud::PAGE_EDIT, $action);
        $this->actionsFactory->add(Crud::PAGE_INDEX, $action);

        $action = Action::new('jsonSeason', new TranslatableMessage('Json'), 'fas fa-server');
        $action->linkToCrudAction('jsonSeason');
        $action->setHtmlAttributes(
            ['target' => '_blank']
        );
        $action->displayIf(static fn ($entity): bool => is_null($entity->getDeletedAt()));

        $this->actionsFactory->add(Crud::PAGE_DETAIL, $action);
        $this->actionsFactory->add(Crud::PAGE_EDIT, $action);
        $this->actionsFactory->add(Crud::PAGE_INDEX, $action);
    }
}
