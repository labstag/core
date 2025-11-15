<?php

namespace Labstag\Controller\Admin\Factory;

use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use ReflectionClass;
use Symfony\Component\DependencyInjection\Attribute\AutowireIterator;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Translation\TranslatableMessage;

class ActionsFactory
{

    protected ?Actions $actions = null;

    protected array $actionsAdd     = [];

    protected array $actionsDefault = [];

    protected ?string $controller = null;

    protected ?string $entity = null;

    protected bool $readOnly = false;

    protected bool $showDetail = true;

    public function __construct(
        #[AutowireIterator('labstag.datas')]
        private readonly iterable $datas,
        protected RequestStack $requestStack,
        protected AdminUrlGenerator $adminUrlGenerator,
    )
    {
    }

    public function add(string $page, string|Action $action): void
    {
        $this->actionsAdd[$page][] = $action;
    }

    public function addDetailMode(): void
    {
        if (!$this->showDetail) {
            return;
        }

        if (!$this->isTrash()) {
            return;
        }

        $this->add(Crud::PAGE_INDEX, Crud::PAGE_DETAIL);
        $this->add(Crud::PAGE_EDIT, Crud::PAGE_DETAIL);
    }

    public function getDefaultActions(): void
    {
        $this->actionsDefault = [
            Crud::PAGE_INDEX  => [
                Action::NEW,
                Action::EDIT,
                Action::DELETE,
            ],
            Crud::PAGE_DETAIL => [
                Action::EDIT,
                Action::DELETE,
                Action::INDEX,
            ],
            Crud::PAGE_EDIT   => [
                Action::SAVE_AND_RETURN,
                Action::SAVE_AND_CONTINUE,
            ],
            Crud::PAGE_NEW    => [
                Action::SAVE_AND_RETURN,
                Action::SAVE_AND_ADD_ANOTHER,
            ],
        ];
    }

    public function init(Actions $actions, string $entity, string $controller): void
    {
        $this->actions    = $actions;
        $this->entity     = $entity;
        $this->controller = $controller;
        $this->getDefaultActions();
        $this->setActionLinkPublic();
        $this->setActionLinkW3CValidator();
    }

    public function isTrash(): bool
    {
        $request = $this->requestStack->getCurrentRequest();
        $current = $request->query->get('action');

        return 'trash' !== $current;
    }

    public function remove(string $page, string $action): void
    {
        if ([] === $this->actionsDefault) {
            $this->getDefaultActions();
        }

        foreach ($this->actionsDefault as $pageDefault => $actions) {
            foreach ($actions as $key => $defaultAction) {
                if ($action === $defaultAction && $pageDefault == $page) {
                    unset($this->actionsDefault[$pageDefault][$key]);
                    $this->actions->remove($page, $action);
                }
            }
        }
    }

    public function setActionLinkPublic(): void
    {
        $find   = false;

        $reflectionClass = new ReflectionClass($this->entity);
        if ($reflectionClass->isAbstract()) {
            return;
        }

        $entity = new $this->entity();
        foreach ($this->datas as $data) {
            if ($data->supportsData($entity)) {
                $find = true;
                break;
            }
        }

        if (!$find || !$this->isTrash()) {
            return;
        }

        $action = Action::new('linkPublic', new TranslatableMessage('View Page'))->setHtmlAttributes(
            ['target' => '_blank']
        );
        $action->linkToCrudAction('linkPublic');

        $this->add(Crud::PAGE_DETAIL, $action);
        $this->add(Crud::PAGE_EDIT, $action);
        $this->add(Crud::PAGE_INDEX, $action);
    }

    public function setActionLinkW3CValidator(): void
    {
        $find   = false;

        $reflectionClass = new ReflectionClass($this->entity);
        if ($reflectionClass->isAbstract()) {
            return;
        }

        $entity = new $this->entity();
        foreach ($this->datas as $data) {
            if ($data->supportsData($entity)) {
                $find = true;
                break;
            }
        }

        if (!$find || !$this->isTrash()) {
            return;
        }

        $w3cAction = Action::new('linkw3CValidator', new TranslatableMessage('W3C Validator'))->setHtmlAttributes(
            ['target' => '_blank']
        );
        $w3cAction->linkToCrudAction('linkw3CValidator');

        $this->add(Crud::PAGE_DETAIL, $w3cAction);
        $this->add(Crud::PAGE_EDIT, $w3cAction);
        $this->add(Crud::PAGE_INDEX, $w3cAction);
    }

    public function setActionUpdateAll(): void
    {
        if (!$this->isTrash()) {
            return;
        }

        $action = Action::new('updateAll', new TranslatableMessage('Update all'), 'fas fa-sync-alt');
        $action->displayAsLink();
        $action->linkToCrudAction('updateAll');
        $action->createAsGlobalAction();
        $this->add(Crud::PAGE_INDEX, $action);
    }

    public function setReadOnly(bool $readOnly): void
    {
        $this->readOnly = $readOnly;
    }

    public function setShowDetail(bool $showDetail): void
    {
        $this->showDetail = $showDetail;
    }

    public function show(): Actions
    {
        $reflectionClass = new ReflectionClass($this->entity);

        if ($reflectionClass->hasMethod('getDeletedAt')) {
            $this->addTrashActions();
            $this->addTrashMode();
        }

        $this->addDetailMode();
        $this->addActions();
        $this->applyReadOnly();

        return $this->actions;
    }

    private function addActions(): void
    {
        foreach ($this->actionsAdd as $page => $actionsToAdd) {
            foreach ($actionsToAdd as $actionToAdd) {
                $this->actions->add($page, $actionToAdd);
            }
        }
    }

    private function addTrashActions(): void
    {
        if (!$this->isTrash()) {
            return;
        }

        $action = Action::new('trash', new TranslatableMessage('Trash'), 'fa fa-trash');
        $this->adminUrlGenerator->setAction(Crud::PAGE_INDEX);
        $this->adminUrlGenerator->setController($this->controller);
        $this->adminUrlGenerator->set('action', 'trash');

        $action->linkToUrl($this->adminUrlGenerator->generateUrl());
        $action->createAsGlobalAction();

        $this->add(Crud::PAGE_INDEX, $action);
    }

    private function addTrashMode(): void
    {
        if ($this->isTrash()) {
            return;
        }

        $this->actions->disable(Action::BATCH_DELETE);
        $this->actions->remove(Crud::PAGE_INDEX, Action::BATCH_DELETE);
        $this->actions->disable(Action::DELETE);

        $action = Action::new('list', new TranslatableMessage('List'), 'fa fa-list');
        $action->linkToCrudAction(Crud::PAGE_INDEX)->createAsGlobalAction();
        $this->add(Crud::PAGE_INDEX, $action);

        $empty = Action::new('empty', new TranslatableMessage('Empty'), 'fa fa-trash');
        $empty->linkToRoute(
            'admin_empty',
            [
                'entity' => $this->entity,
            ]
        );
        $empty->createAsGlobalAction();
        $this->add(Crud::PAGE_INDEX, $empty);

        $restore = Action::new('restore', new TranslatableMessage('Restore'));
        $restore->linkToRoute(
            'admin_restore',
            static fn ($entity): array => [
                'uuid'   => $entity->getId(),
                'entity' => $entity::class,
            ]
        );
        $this->add(Crud::PAGE_INDEX, $restore);

        $this->remove(Crud::PAGE_INDEX, Action::NEW);
        $this->remove(Crud::PAGE_INDEX, Action::EDIT);
    }

    private function applyReadOnly(): void
    {
        if (!$this->readOnly) {
            return;
        }

        $this->remove(Crud::PAGE_INDEX, Action::NEW);
        $this->remove(Crud::PAGE_INDEX, Action::EDIT);
        $this->remove(Crud::PAGE_EDIT, Action::SAVE_AND_CONTINUE);
        $this->remove(Crud::PAGE_EDIT, Action::SAVE_AND_RETURN);
    }
}
