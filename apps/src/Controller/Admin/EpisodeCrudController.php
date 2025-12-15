<?php

namespace Labstag\Controller\Admin;

use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Filter\DateTimeFilter;
use Labstag\Entity\Episode;
use Labstag\Field\WysiwygField;
use Labstag\Filter\SeasonEpisodeFilter;
use Labstag\Filter\SerieEpisodeFilter;
use Labstag\Message\EpisodeMessage;
use Override;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Translation\TranslatableMessage;

class EpisodeCrudController extends CrudControllerAbstract
{
    #[Override]
    public function configureActions(Actions $actions): Actions
    {
        $this->actionsFactory->init($actions, self::getEntityFqcn(), static::class);
        $this->actionsFactory->setReadOnly(true);
        $this->setUpdateAction();

        return $this->actionsFactory->show();
    }

    #[Override]
    public function configureCrud(Crud $crud): Crud
    {
        $crud = parent::configureCrud($crud);
        $crud->setEntityLabelInSingular(new TranslatableMessage('Episode'));
        $crud->setEntityLabelInPlural(new TranslatableMessage('Episodes'));
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

        $seasonField = TextField::new('refseason', new TranslatableMessage('Serie'));
        $seasonField->formatValue(
            function ($value, $entity) {
                unset($value);
                if (is_null($entity)) {
                    return '';
                }

                $season = $entity->getRefseason();
                if (is_null($season)) {
                    return '';
                }

                $serie = $season->getRefserie();
                if (is_null($serie)) {
                    return '';
                }

                return $serie->getTitle();
            }
        );
        $integerField = IntegerField::new('runtime', new TranslatableMessage('Runtime'));
        $integerField->setTemplatePath('admin/field/runtime-episode.html.twig');

        $wysiwgTranslation = new TranslatableMessage('Overview');
        $wysiwgField = WysiwygField::new('overview', $wysiwgTranslation->getMessage());
        $wysiwgField->hideOnIndex();

        $this->crudFieldFactory->addFieldsToTab(
            'principal',
            [
                $this->crudFieldFactory->booleanField('enable', new TranslatableMessage('Enable')),
                $this->crudFieldFactory->titleField(),
                $this->crudFieldFactory->imageField('img', $pageName, self::getEntityFqcn()),
                $textField,
                $seasonField,
                AssociationField::new('refseason', new TranslatableMessage('Season')),
                IntegerField::new('number', new TranslatableMessage('Number')),
                DateField::new('air_date', new TranslatableMessage('Air date')),
                $integerField,
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
        $seasonTranslation = new TranslatableMessage('Season');
        $filters->add(
            SerieEpisodeFilter::new('number', $seasonTranslation->getMessage())->setChoices(
                array_merge(
                    ['' => ''],
                    $this->seasonService->getSeasonsChoice()
                )
            )
        );
        $serieTranslation = new TranslatableMessage('Serie');
        $filters->add(DateTimeFilter::new('airDate', new TranslatableMessage('Air date')));
        $filters->add(
            SeasonEpisodeFilter::new('serie', $serieTranslation->getMessage())->setChoices(
                array_merge(
                    ['' => ''],
                    $this->serieService->getSeriesChoice()
                )
            )
        );

        return $filters;
    }

    public static function getEntityFqcn(): string
    {
        return Episode::class;
    }

    public function jsonEpisode(Request $request): JsonResponse
    {
        $entityId                          = $request->query->get('entityId');
        $repositoryAbstract                = $this->getRepository();
        $episode                           = $repositoryAbstract->find($entityId);
        $details                           = $this->theMovieDbApi->getDetailsEpisode($episode);

        return new JsonResponse($details);
    }

    public function updateEpisode(Request $request): RedirectResponse
    {
        $entityId                          = $request->query->get('entityId');
        $repositoryAbstract                = $this->getRepository();
        $episode                           = $repositoryAbstract->find($entityId);
        $this->messageBus->dispatch(new EpisodeMessage($episode->getId()));
        if ($request->headers->has('referer')) {
            $url = $request->headers->get('referer');
            if (is_string($url) && '' !== $url) {
                return $this->redirect($url);
            }
        }

        return $this->redirectToRoute('admin_episode_index');
    }

    private function setUpdateAction(): void
    {
        if (!$this->actionsFactory->isTrash()) {
            return;
        }

        $action = Action::new('updateEpisode', new TranslatableMessage('Update'), 'fas fa-sync-alt');
        $action->linkToCrudAction('updateEpisode');
        $action->displayIf(static fn ($entity): bool => is_null($entity->getDeletedAt()));

        $this->actionsFactory->add(Crud::PAGE_DETAIL, $action);
        $this->actionsFactory->add(Crud::PAGE_EDIT, $action);
        $this->actionsFactory->add(Crud::PAGE_INDEX, $action);

        $action = Action::new('jsonEpisode', new TranslatableMessage('Json'), 'fas fa-server');
        $action->linkToCrudAction('jsonEpisode');
        $action->setHtmlAttributes(
            ['target' => '_blank']
        );
        $action->displayIf(static fn ($entity): bool => is_null($entity->getDeletedAt()));

        $this->actionsFactory->add(Crud::PAGE_DETAIL, $action);
        $this->actionsFactory->add(Crud::PAGE_EDIT, $action);
        $this->actionsFactory->add(Crud::PAGE_INDEX, $action);
    }
}
