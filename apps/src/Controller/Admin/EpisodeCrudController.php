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
use Labstag\Controller\Admin\Abstract\AbstractCrudControllerLib;
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

class EpisodeCrudController extends AbstractCrudControllerLib
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
        yield $this->addTabPrincipal();
        yield $this->crudFieldFactory->titleField();
        yield $this->crudFieldFactory->booleanField('enable', (string) new TranslatableMessage('Enable'));
        yield $this->crudFieldFactory->imageField('img', $pageName, self::getEntityFqcn());
        yield TextField::new('tmdb', new TranslatableMessage('Tmdb'))->hideOnIndex();
        $textField = TextField::new('refseason', new TranslatableMessage('Serie'));
        $textField->setFormTypeOption('choice_label', 'refserie');
        $textField->formatValue(
            function ($value, $entity) {
                unset($value);

                return $entity->getRefseason()?->getRefserie();
            }
        );
        yield $textField;
        yield AssociationField::new('refseason', new TranslatableMessage('Season'));
        yield IntegerField::new('number', new TranslatableMessage('Number'));
        yield DateField::new('air_date', new TranslatableMessage('Air date'));
        yield IntegerField::new('runtime', new TranslatableMessage('Runtime'))->hideOnIndex()->hideOnDetail();
        $episodeCollectionField = CollectionField::new('runtime', new TranslatableMessage('Runtime'));
        $episodeCollectionField->setTemplatePath('admin/field/runtime-episode.html.twig');
        $episodeCollectionField->hideOnForm();
        yield $episodeCollectionField;
        yield WysiwygField::new('overview', new TranslatableMessage('Overview'))->hideOnIndex();
        foreach ($this->crudFieldFactory->dateSet($pageName) as $field) {
            yield $field;
        }
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
        $serviceEntityRepositoryLib   = $this->getRepository();
        $episode                      = $serviceEntityRepositoryLib->find($entity);
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
