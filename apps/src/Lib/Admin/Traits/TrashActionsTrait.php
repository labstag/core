<?php

namespace Labstag\Lib\Admin\Traits;

use Doctrine\ORM\QueryBuilder;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Dto\SearchDto;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Translation\TranslatableMessage;

/**
 * Management of trash-related actions (soft delete) and associated filtering.
 * The consuming class must provide:
 *  - generateUrl(...)
 *  - getEntityFqcn().
 */
trait TrashActionsTrait
{
    protected function configureTrashActions(Actions $actions, Request $request, AdminUrlGenerator $urlGenerator): void
    {
        $this->addTrashToggleAction($actions, $request, $urlGenerator);
        $this->addTrashModeActions($actions, $request);
        $this->configureNavigationActions($actions);
    }

    protected function filterTrash(SearchDto $searchDto, QueryBuilder $queryBuilder): QueryBuilder
    {
        $action = $searchDto->getRequest()->query->get('action');
        if ('trash' === $action) {
            $queryBuilder->andWhere('entity.deletedAt IS NOT NULL');
        }

        return $queryBuilder;
    }

    private function addTrashModeActions(Actions $actions, Request $request): void
    {
        $current = $request->query->get('action');
        if (empty($current)) {
            return;
            // not in trash mode
        }

        $action = Action::new('list', new TranslatableMessage('List'), 'fa fa-list');
        $action->linkToCrudAction(Crud::PAGE_INDEX)->createAsGlobalAction();
        $actions->add(Crud::PAGE_INDEX, $action);

        $empty = Action::new('empty', new TranslatableMessage('Empty'), 'fa fa-trash');
        $empty->linkToRoute(
            'admin_empty',
            [
                'entity' => $this->getEntityFqcn(),
            ]
        )->createAsGlobalAction();
        $actions->add(Crud::PAGE_INDEX, $empty);

        $restore = Action::new('restore', new TranslatableMessage('Restore'));
        $restore->linkToRoute(
            'admin_restore',
            static fn ($entity): array => [
                'uuid'   => $entity->getId(),
                'entity' => $entity::class,
            ]
        );
        $actions->add(Crud::PAGE_INDEX, $restore);

        // remove New/Edit to avoid direct modifications on deleted elements
        $actions->remove(Crud::PAGE_INDEX, Action::NEW);
        $actions->remove(Crud::PAGE_INDEX, Action::EDIT);
    }

    private function addTrashToggleAction(Actions $actions, Request $request, AdminUrlGenerator $urlGenerator): void
    {
        $current = $request->query->get('action');
        if ('trash' === $current) {
            return;
            // already in trash mode
        }

        $action = Action::new('trash', new TranslatableMessage('Trash'), 'fa fa-trash');
        $urlGenerator->setAction(Crud::PAGE_INDEX);
        $urlGenerator->setController(static::class);
        $urlGenerator->set('action', 'trash');

        $action->linkToUrl($urlGenerator->generateUrl());
        $action->createAsGlobalAction();

        $actions->add(Crud::PAGE_INDEX, $action);
    }

    private function configureNavigationActions(Actions $actions): void
    {
        $actions->add(Crud::PAGE_EDIT, Action::INDEX);
        $actions->add(Crud::PAGE_INDEX, Action::DETAIL);
        $actions->add(Crud::PAGE_NEW, Action::INDEX);
    }
}
