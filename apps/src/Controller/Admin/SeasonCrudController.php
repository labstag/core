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
use EasyCorp\Bundle\EasyAdminBundle\Filter\EntityFilter;
use Labstag\Controller\Admin\Abstract\AbstractCrudControllerLib;
use Labstag\Entity\Meta;
use Labstag\Entity\Season;
use Labstag\Field\WysiwygField;
use Labstag\Message\SeasonMessage;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Translation\TranslatableMessage;

class SeasonCrudController extends AbstractCrudControllerLib
{
    #[\Override]
    public function configureActions(Actions $actions): Actions
    {
        $this->setActionPublic($actions, 'admin_season_w3c', 'admin_season_public');
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
        $crud->setEntityLabelInSingular(new TranslatableMessage('Season'));
        $crud->setEntityLabelInPlural(new TranslatableMessage('Seasons'));
        $crud->setDefaultSort(
            ['number' => 'ASC']
        );

        return $crud;
    }

    #[\Override]
    public function configureFields(string $pageName): iterable
    {
        yield $this->addTabPrincipal();
        yield $this->crudFieldFactory->slugField();
        yield $this->crudFieldFactory->titleField();
        yield $this->crudFieldFactory->booleanField('enable', (string) new TranslatableMessage('Enable'));
        yield $this->crudFieldFactory->imageField('img', $pageName, self::getEntityFqcn());
        yield TextField::new('tmdb', new TranslatableMessage('Tmdb'))->hideOnIndex();
        yield AssociationField::new('refserie', new TranslatableMessage('Serie'));
        yield IntegerField::new('number', new TranslatableMessage('Number'));
        yield DateField::new('air_date', new TranslatableMessage('Air date'));
        $episodeCollectionField = CollectionField::new('episodes', new TranslatableMessage('Episodes'));
        $episodeCollectionField->setTemplatePath('admin/field/episodes.html.twig');
        $episodeCollectionField->hideOnForm();
        yield $episodeCollectionField;
        $episodeCollectionField = CollectionField::new('runtime', new TranslatableMessage('Runtime'));
        $episodeCollectionField->setTemplatePath('admin/field/runtime-season.html.twig');
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
        $filters->add(EntityFilter::new('refserie', new TranslatableMessage('Serie')));

        return $filters;
    }

    #[\Override]
    public function createEntity(string $entityFqcn): Season
    {
        $season = new $entityFqcn();
        $meta   = new Meta();
        $season->setMeta($meta);

        return $season;
    }

    public static function getEntityFqcn(): string
    {
        return Season::class;
    }

    #[Route('/admin/season/{entity}/public', name: 'admin_season_public')]
    public function linkPublic(string $entity): RedirectResponse
    {
        $serviceEntityRepositoryLib  = $this->getRepository();
        $season                      = $serviceEntityRepositoryLib->find($entity);

        return $this->publicLink($season);
    }

    #[Route('/admin/season/{entity}/update', name: 'admin_season_update')]
    public function update(string $entity, Request $request, MessageBusInterface $messageBus): RedirectResponse
    {
        $serviceEntityRepositoryLib  = $this->getRepository();
        $season                      = $serviceEntityRepositoryLib->find($entity);
        $messageBus->dispatch(new SeasonMessage($season->getId()));
        if ($request->headers->has('referer')) {
            $url = $request->headers->get('referer');
            if (is_string($url) && '' !== $url) {
                return $this->redirect($url);
            }
        }

        return $this->redirectToRoute('admin_season_index');
    }

    #[Route('/admin/season/{entity}/w3c', name: 'admin_season_w3c')]
    public function w3c(string $entity): RedirectResponse
    {
        $serviceEntityRepositoryLib  = $this->getRepository();
        $season                      = $serviceEntityRepositoryLib->find($entity);

        return $this->linkw3CValidator($season);
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
            fn (Season $season): string => $this->generateUrl(
                'admin_season_update',
                [
                    'entity' => $season->getId(),
                ]
            )
        );
        $action->displayIf(static fn ($entity): bool => is_null($entity->getDeletedAt()));

        return $action;
    }
}
