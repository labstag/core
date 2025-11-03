<?php

namespace Labstag\Controller\Admin;

use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\CollectionField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use Labstag\Entity\Episode;
use Labstag\Field\WysiwygField;
use Labstag\Filter\SeasonEpisodeFilter;
use Labstag\Filter\SerieEpisodeFilter;
use Labstag\Message\EpisodeMessage;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Translation\TranslatableMessage;

class EpisodeCrudController extends CrudControllerAbstract
{
    #[\Override]
    public function configureActions(Actions $actions): Actions
    {
        $this->setEditDetail($actions);
        $action = $this->setUpdateAction();
        $actions->add(Crud::PAGE_DETAIL, $action);
        $actions->add(Crud::PAGE_EDIT, $action);
        $actions->add(Crud::PAGE_INDEX, $action);
        $actions->remove(Crud::PAGE_INDEX, Action::NEW);
        $actions->remove(Crud::PAGE_INDEX, Action::EDIT);
        $actions->remove(Crud::PAGE_DETAIL, Action::EDIT);
        $this->configureActionsTrash($actions);
        $this->configureActionsUpdateImage();

        return $actions;
    }

    #[\Override]
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

    #[\Override]
    public function configureFields(string $pageName): iterable
    {
        $this->crudFieldFactory->setTabPrincipal(self::getEntityFqcn());

        $textField = TextField::new('tmdb', new TranslatableMessage('Tmdb'));
        $textField->hideOnIndex();

        $reasonField = TextField::new('refseason', new TranslatableMessage('Serie'));
        $reasonField->setFormTypeOption('choice_label', 'refserie');
        $reasonField->formatValue(
            function ($value, $entity) {
                unset($value);

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
        $integerField->hideOnIndex();
        $integerField->hideOnDetail();

        $collectionField = CollectionField::new('runtime', new TranslatableMessage('Runtime'));
        $collectionField->setTemplatePath('admin/field/runtime-episode.html.twig');
        $collectionField->hideOnForm();

        $wysiwygField = WysiwygField::new('overview', new TranslatableMessage('Overview'));
        $wysiwygField->hideOnIndex();

        $this->crudFieldFactory->addFieldsToTab(
            'principal',
            [
                $this->crudFieldFactory->booleanField('enable', (string) new TranslatableMessage('Enable')),
                $this->crudFieldFactory->titleField(),
                $this->crudFieldFactory->imageField('img', $pageName, self::getEntityFqcn()),
                $textField,
                $reasonField,
                AssociationField::new('refseason', new TranslatableMessage('Season')),
                IntegerField::new('number', new TranslatableMessage('Number')),
                DateField::new('air_date', new TranslatableMessage('Air date')),
                $integerField,
                $collectionField,
                $wysiwygField,
            ]
        );

        $this->crudFieldFactory->setTabDate($pageName);

        yield from $this->crudFieldFactory->getConfigureFields();
    }

    #[\Override]
    public function configureFilters(Filters $filters): Filters
    {
        $this->crudFieldFactory->addFilterEnable($filters);
        $filters->add(
            SerieEpisodeFilter::new('number', new TranslatableMessage('Season'))->setChoices(
                $this->seasonService->getSeasonsChoice()
            )
        );
        $filters->add(
            SeasonEpisodeFilter::new('serie', new TranslatableMessage('Serie'))->setChoices(
                $this->serieService->getSeriesChoice()
            )
        );

        return $filters;
    }

    public static function getEntityFqcn(): string
    {
        return Episode::class;
    }

    #[Route('/admin/episode/{entity}/update', name: 'admin_episode_update')]
    public function update(string $entity, Request $request, MessageBusInterface $messageBus): RedirectResponse
    {
        $RepositoryAbstract                = $this->getRepository();
        $episode                           = $RepositoryAbstract->find($entity);
        $messageBus->dispatch(new EpisodeMessage($episode->getId()));
        if ($request->headers->has('referer')) {
            $url = $request->headers->get('referer');
            if (is_string($url) && '' !== $url) {
                return $this->redirect($url);
            }
        }

        return $this->redirectToRoute('admin_episode_index');
    }

    private function configureActionsUpdateImage(): void
    {
        $request = $this->container->get('request_stack')->getCurrentRequest();
        $request->query->get('action', null);
    }

    private function setUpdateAction(): Action
    {
        $action = Action::new('update', new TranslatableMessage('Update'));
        $action->linkToUrl(
            fn (Episode $episode): string => $this->generateUrl(
                'admin_episode_update',
                [
                    'entity' => $episode->getId(),
                ]
            )
        );
        $action->displayIf(static fn ($entity): bool => is_null($entity->getDeletedAt()));

        return $action;
    }
}
