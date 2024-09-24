<?php

namespace Labstag\Lib;

use Doctrine\ORM\QueryBuilder;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FieldCollection;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FilterCollection;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Dto\EntityDto;
use EasyCorp\Bundle\EasyAdminBundle\Dto\SearchDto;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use Override;
use Symfony\Component\HttpFoundation\Request;

abstract class AbstractCrudControllerLib extends AbstractCrudController
{
    #[Override]
    public function createIndexQueryBuilder(
        SearchDto $searchDto,
        EntityDto $entityDto,
        FieldCollection $fieldCollection,
        FilterCollection $filterCollection
    ): QueryBuilder
    {
        $queryBuilder = parent::createIndexQueryBuilder($searchDto, $entityDto, $fieldCollection, $filterCollection);
        $request      = $searchDto->getRequest();
        $action       = $request->query->get('action', null);
        if ('trash' == $action) {
            $queryBuilder->andWhere('entity.deletedAt IS NOT NULL');
        }

        return $queryBuilder;
    }

    protected function configureActionsTrash(Actions $actions): void
    {
        $request = $this->container->get('request_stack')->getCurrentRequest();
        $this->configureActionsTrashBtn($request, $actions);
        $this->configureActionsTrashEmptyBtn($request, $actions);
    }

    protected function configureActionsTrashBtn(Request $request, Actions $actions): void
    {
        $action = $request->query->get('action', null);
        if ('trash' == $action) {
            return;
        }

        $action    = Action::new('trash', 'Trash', 'fa fa-trash');
        $generator = $this->container->get(AdminUrlGenerator::class);
        $generator->setAction(Action::INDEX);
        $generator->setController(static::class);
        $generator->set('action', 'trash');

        $action->linkToUrl($generator->generateUrl());
        $action->createAsGlobalAction();

        $actions->add(Crud::PAGE_INDEX, $action);
    }

    protected function configureActionsTrashEmptyBtn(Request $request, Actions $actions): void
    {
        $action = $request->query->get('action', null);
        if (empty($action)) {
            return;
        }

        $action = Action::new('list', 'Liste', 'fa fa-list');
        $action->linkToCrudAction(Crud::PAGE_INDEX);
        $action->createAsGlobalAction();

        $actions->add(Crud::PAGE_INDEX, $action);

        $action = Action::new('empty', 'Vider', 'fa fa-trash');
        $action->linkToRoute(
            'admin_empty',
            [
                'class' => static::class,
            ]
        );
        $action->createAsGlobalAction();

        $actions->add(Crud::PAGE_INDEX, $action);
        $actions->remove(Crud::PAGE_INDEX, Action::NEW);
        $actions->remove(Crud::PAGE_INDEX, Action::EDIT);

        $action = Action::new('restore', 'Restore');
        $action->linkToRoute(
            'admin_restore',
            static fn ($entity) => [
                'uuid'   => $entity->getId(),
                'entity' => $entity::class,
            ]
        );
        $actions->add(Crud::PAGE_INDEX, $action);
    }
}
