<?php

namespace Labstag\Controller\Admin;

use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use Labstag\Entity\Game;
use Labstag\Entity\Platform;
use Labstag\Field\WysiwygField;
use Labstag\Form\Admin\GameImportType;
use Labstag\Form\Admin\GameOtherPlatformType;
use Labstag\Form\Admin\GameType;
use Labstag\Message\AddGameMessage;
use Labstag\Message\GameAllMessage;
use Labstag\Message\GameMessage;
use Labstag\Message\ImportMessage;
use Override;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Translation\TranslatableMessage;

class GameCrudController extends CrudControllerAbstract
{
    public function addAnotherPlatform(Request $request): JsonResponse
    {
        $entityId           = $request->query->get('entityId');
        $repositoryAbstract = $this->getRepository();
        $game               = $repositoryAbstract->find($entityId);
        if (!$game instanceof Game) {
            return new JsonResponse(
                [
                    'status'  => 'error',
                    'message' => $this->translator->trans(new TranslatableMessage('Game not found')),
                ]
            );
        }

        $post = $request->request->all();
        if (!isset($post['game_other_platform'])) {
            return new JsonResponse(
                [
                    'status'  => 'error',
                    'message' => $this->translator->trans(new TranslatableMessage('No data found')),
                ]
            );
        }

        if (!isset($post['game_other_platform']['platforms'])) {
            return new JsonResponse(
                [
                    'status'  => 'error',
                    'message' => $this->translator->trans(new TranslatableMessage('No platform selected')),
                ]
            );
        }

        $platforms          = $post['game_other_platform']['platforms'];
        foreach ($platforms as $platform) {
            $platformEntity = $this->getRepository(Platform::class)->find($platform);
            if ($platformEntity instanceof Platform) {
                $game->addPlatform($platformEntity);
            }
        }

        $repositoryAbstract->save($game);

        return new JsonResponse(
            [
                'status'  => 'success',
                'message' => $this->translator->trans(new TranslatableMessage('Platforms added successfully')),
            ]
        );
    }

    public function addByApi(Request $request): JsonResponse
    {
        $id       = $request->query->get('id');
        $platform = $request->query->get('platform', '');
        $this->messageBus->dispatch(new AddGameMessage($id, 'game', $platform));

        return new JsonResponse(
            [
                'status'  => 'success',
                'id'      => $id,
                'message' => $this->translator->trans(new TranslatableMessage('Game is being added')),
            ]
        );
    }

    public function addToAnotherPlatform(Request $request): Response
    {
        $entityId               = $request->query->get('entityId');
        $game                   = $this->getRepository(Game::class)->find($entityId);
        $platforms              = $this->getRepository(Platform::class)->notInGame($game);
        $form                   = $this->createForm(
            type: GameOtherPlatformType::class,
            options: ['platforms' => $platforms]
        );
        $form->handleRequest($request);

        return $this->render(
            'admin/game/other_platforms.html.twig',
            [
                'game' => $game,
                'form' => $form->createView(),
            ]
        );
    }

    public function apiGame(Request $request): Response
    {
        $all     = $request->request->all();
        $page    = $request->query->get('page', 1);
        $limit   = $request->query->get('limit', 50);
        $offset  = ($page - 1) * $limit;
        $all     = $request->request->all();
        $data    = [
            'title'     => $all['game']['title'] ?? '',
            'platform'  => $all['game']['platform'] ?? '',
            'franchise' => $all['game']['franchise'] ?? '',
            'type'      => $all['game']['type'] ?? '',
            'number'    => $all['game']['number'] ?? '',
        ];
        $games   = $this->gameService->getGameApi($data, $limit, $offset);

        return $this->render(
            'admin/api/game/list.html.twig',
            [
                'page'       => $page,
                'platform'   => $all['game']['platform'] ?? '',
                'controller' => self::class,
                'games'      => $games,
            ]
        );
    }

    #[Override]
    public function configureActions(Actions $actions): Actions
    {
        $this->actionsFactory->init($actions, self::getEntityFqcn(), static::class);
        $this->actionsFactory->remove(Crud::PAGE_INDEX, Action::NEW);
        $this->setLinkIgdb();
        $this->setUpdateAction();
        $this->addActionNewGame();
        $this->addActionImportGame();
        $this->actionsFactory->setActionUpdateAll('updateAllGame');

        return $this->actionsFactory->show();
    }

    #[Override]
    public function configureCrud(Crud $crud): Crud
    {
        $crud = parent::configureCrud($crud);
        $crud->setDefaultSort(
            ['title' => 'ASC']
        );
        $crud->setEntityLabelInSingular(new TranslatableMessage('Game'));
        $crud->setEntityLabelInPlural(new TranslatableMessage('Games'));

        return $crud;
    }

    #[Override]
    public function configureFields(string $pageName): iterable
    {
        $this->crudFieldFactory->setTabPrincipal($this->getContext());

        $textField = TextField::new('igdb', new TranslatableMessage('Igdb'));
        $textField->hideOnIndex();

        $associationField = AssociationField::new('platforms', new TranslatableMessage('Platforms'));
        $associationField->setTemplatePath('admin/field/game-platforms.html.twig');

        $franchisesField = AssociationField::new('franchises', new TranslatableMessage('Franchises'));
        $franchisesField->setTemplatePath('admin/field/game-franchises.html.twig');

        $this->crudFieldFactory->setTabDate($pageName);

        $wysiwygField = WysiwygField::new('summary', new TranslatableMessage('Summary'));
        $wysiwygField->hideOnIndex();

        $this->crudFieldFactory->addFieldsToTab(
            'principal',
            [
                $this->crudFieldFactory->slugField(),
                $this->crudFieldFactory->booleanField('enable', new TranslatableMessage('Enable')),
                $this->crudFieldFactory->titleField(),
                $wysiwygField,
                $this->crudFieldFactory->imageField('img', $pageName, self::getEntityFqcn()),
                DateField::new('release_date', new TranslatableMessage('Release date')),
                $textField,
                $associationField,
                $franchisesField,
                $this->crudFieldFactory->categoriesFieldForPage(self::getEntityFqcn(), $pageName),
            ]
        );

        yield from $this->crudFieldFactory->getConfigureFields($pageName);
    }

    #[Override]
    public function configureFilters(Filters $filters): Filters
    {
        $this->crudFieldFactory->addFilterEnable($filters);
        $filters->add('release_date');
        $filters->add('platforms');
        $filters->add('franchises');

        return $filters;
    }

    public static function getEntityFqcn(): string
    {
        return Game::class;
    }

    public function igdb(Request $request): Response
    {
        $entityId                        = $request->query->get('entityId');
        $repositoryAbstract              = $this->getRepository();
        $game                            = $repositoryAbstract->find($entityId);
        $url                             = $game->getUrl();
        if (empty($url)) {
            return $this->redirectToRoute('admin_game_index');
        }

        return $this->redirect($url);
    }

    public function importFile(Request $request): JsonResponse
    {
        $files   = $request->files->all();
        $all     = $request->request->all();
        $file    = $files['game_import']['file'] ?? null;
        $data    = [
            'platform' => $all['game_import']['platform'] ?? '',
        ];
        if (null === $file) {
            return new JsonResponse(
                [
                    'status'  => 'error',
                    'message' => 'No file uploaded',
                ]
            );
        }

        $content   = file_get_contents($file->getPathname());
        $extension = $file->getClientOriginalExtension();
        $filename  = uniqid('import_', true) . '.' . $extension;
        $this->fileService->saveFileInAdapter('private', $filename, $content);
        $this->messageBus->dispatch(new ImportMessage($filename, 'game', $data));

        return new JsonResponse(
            [
                'status'  => 'success',
                'message' => 'Import started',
            ]
        );
    }

    public function jsonMovie(Request $request): JsonResponse
    {
        $entityId                        = $request->query->get('entityId');
        $repositoryAbstract              = $this->getRepository();
        $game                            = $repositoryAbstract->find($entityId);
        $details                         = $this->gameService->getApiGameId($game->getIgdb());

        return new JsonResponse($details);
    }

    public function setLinkIgdb(): void
    {
        if (!$this->actionsFactory->isTrash()) {
            return;
        }

        $action = Action::new('igdb', new TranslatableMessage('IGDB Page'), 'fas fa-external-link-alt');
        $action->setHtmlAttributes(
            ['target' => '_blank']
        );
        $action->linkToCrudAction('igdb');
        $action->displayIf(static fn ($entity): bool => is_null($entity->getDeletedAt()));

        $this->actionsFactory->add(Crud::PAGE_DETAIL, $action);
        $this->actionsFactory->add(Crud::PAGE_EDIT, $action);
        $this->actionsFactory->add(Crud::PAGE_INDEX, $action);

        $action = Action::new(
            'addToAnotherPlatform',
            new TranslatableMessage('Add to another platform'),
            'fas fa-plus-circle'
        );
        $action->linkToCrudAction('addToAnotherPlatform');
        $action->setHtmlAttributes(
            ['data-action' => 'show-modal']
        );

        $this->actionsFactory->add(Crud::PAGE_DETAIL, $action);
        $this->actionsFactory->add(Crud::PAGE_EDIT, $action);
        $this->actionsFactory->add(Crud::PAGE_INDEX, $action);
    }

    public function showModalGame(Request $request): Response
    {
        $form    = $this->createForm(GameType::class);
        $form->handleRequest($request);

        return $this->render(
            'admin/game/new.html.twig',
            [
                'controller' => self::class,
                'form'       => $form->createView(),
            ]
        );
    }

    public function showModalImportGame(Request $request): Response
    {
        $form    = $this->createForm(GameImportType::class);
        $form->handleRequest($request);

        return $this->render(
            'admin/game/import.html.twig',
            [
                'controller' => self::class,
                'form'       => $form->createView(),
            ]
        );
    }

    public function updateAllGame(): RedirectResponse
    {
        $this->messageBus->dispatch(new GameAllMessage());

        return $this->redirectToRoute('admin_game_index');
    }

    public function updateGame(Request $request): RedirectResponse
    {
        $entityId                        = $request->query->get('entityId');
        $repositoryAbstract              = $this->getRepository();
        $game                            = $repositoryAbstract->find($entityId);
        $this->messageBus->dispatch(new GameMessage($game->getId()));
        if ($request->headers->has('referer')) {
            $url = $request->headers->get('referer');
            if (is_string($url) && '' !== $url) {
                return $this->redirect($url);
            }
        }

        return $this->redirectToRoute('admin_game_index');
    }

    private function addActionImportGame(): void
    {
        if (!$this->actionsFactory->isTrash()) {
            return;
        }

        $action = Action::new('showModalImportGame', new TranslatableMessage('Import'), 'fas fa-file-import');
        $action->linkToCrudAction('showModalImportGame');
        $action->setHtmlAttributes(
            ['data-action' => 'show-modal']
        );
        $action->createAsGlobalAction();

        $this->actionsFactory->add(Crud::PAGE_INDEX, $action);
    }

    private function addActionNewGame(): void
    {
        if (!$this->actionsFactory->isTrash()) {
            return;
        }

        $action = Action::new('showModalGame', new TranslatableMessage('New game'), 'fas fa-plus-circle');
        $action->linkToCrudAction('showModalGame');
        $action->setHtmlAttributes(
            ['data-action' => 'show-modal']
        );
        $action->createAsGlobalAction();

        $this->actionsFactory->add(Crud::PAGE_INDEX, $action);
    }

    private function setUpdateAction(): void
    {
        if (!$this->actionsFactory->isTrash()) {
            return;
        }

        $action = Action::new('updateGame', new TranslatableMessage('Update'), 'fas fa-sync-alt');
        $action->linkToCrudAction('updateGame');
        $action->displayIf(static fn ($entity): bool => is_null($entity->getDeletedAt()));

        $this->actionsFactory->add(Crud::PAGE_DETAIL, $action);
        $this->actionsFactory->add(Crud::PAGE_EDIT, $action);
        $this->actionsFactory->add(Crud::PAGE_INDEX, $action);

        $action = Action::new('jsonMovie', new TranslatableMessage('Json'), 'fas fa-server');
        $action->linkToCrudAction('jsonMovie');
        $action->setHtmlAttributes(
            ['target' => '_blank']
        );
        $action->displayIf(static fn ($entity): bool => is_null($entity->getDeletedAt()));

        $this->actionsFactory->add(Crud::PAGE_DETAIL, $action);
        $this->actionsFactory->add(Crud::PAGE_EDIT, $action);
        $this->actionsFactory->add(Crud::PAGE_INDEX, $action);
    }
}
