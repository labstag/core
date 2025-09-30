<?php

namespace Labstag\Lib\Admin\Traits;

use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Dto\SearchDto;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Translation\TranslatableMessage;
use Doctrine\ORM\QueryBuilder;

/**
 * Gestion des actions liées à la corbeille (soft delete) et filtrage associé.
 * La classe consommatrice doit fournir :
 *  - generateUrl(...)
 *  - getEntityFqcn()
 */
trait TrashActionsTrait
{
    protected function configureTrashActions(Actions $actions, Request $request, AdminUrlGenerator $urlGenerator): void
    {
        $this->addTrashToggleAction($actions, $request, $urlGenerator);
        $this->addTrashModeActions($actions, $request);
        $this->configureNavigationActions($actions);
    }

    protected function filterTrash(SearchDto $searchDto, QueryBuilder $qb): QueryBuilder
    {
        $action = $searchDto->getRequest()->query->get('action');
        if ('trash' === $action) {
            $qb->andWhere('entity.deletedAt IS NOT NULL');
        }

        return $qb;
    }

    private function addTrashToggleAction(Actions $actions, Request $request, AdminUrlGenerator $urlGenerator): void
    {
        $current = $request->query->get('action');
        if ('trash' === $current) {
            return; // déjà en mode corbeille
        }

        $action = Action::new('trash', new TranslatableMessage('Trash'), 'fa fa-trash');
        $urlGenerator->setAction(Crud::PAGE_INDEX);
        $urlGenerator->setController(static::class);
        $urlGenerator->set('action', 'trash');
        $action->linkToUrl($urlGenerator->generateUrl());
        $action->createAsGlobalAction();

        $actions->add(Crud::PAGE_INDEX, $action);
    }

    private function addTrashModeActions(Actions $actions, Request $request): void
    {
        $current = $request->query->get('action');
        if (empty($current)) {
            return; // pas en mode corbeille
        }

        $list = Action::new('list', new TranslatableMessage('List'), 'fa fa-list');
        $list->linkToCrudAction(Crud::PAGE_INDEX)->createAsGlobalAction();
        $actions->add(Crud::PAGE_INDEX, $list);

        $empty = Action::new('empty', new TranslatableMessage('Empty'), 'fa fa-trash');
        $empty->linkToRoute('admin_empty', [
            'entity' => $this->getEntityFqcn(),
        ])->createAsGlobalAction();
        $actions->add(Crud::PAGE_INDEX, $empty);

        $restore = Action::new('restore', new TranslatableMessage('Restore'));
        $restore->linkToRoute('admin_restore', static fn ($entity): array => [
            'uuid'   => $entity->getId(),
            'entity' => $entity::class,
        ]);
        $actions->add(Crud::PAGE_INDEX, $restore);

        // retirer New/Edit pour éviter modifications directes sur éléments supprimés
        $actions->remove(Crud::PAGE_INDEX, Action::NEW);
        $actions->remove(Crud::PAGE_INDEX, Action::EDIT);
    }

    private function configureNavigationActions(Actions $actions): void
    {
        $actions->add(Crud::PAGE_EDIT, Action::INDEX);
        $actions->add(Crud::PAGE_INDEX, Action::DETAIL);
        $actions->add(Crud::PAGE_NEW, Action::INDEX);
    }
}
