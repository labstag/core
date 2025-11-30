<?php

namespace Labstag\Controller\Admin;

use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use Labstag\Entity\Game;
use Labstag\Entity\Platform;
use Labstag\Form\Admin\GameImportType;
use Labstag\Form\Admin\GameOtherPlatformType;
use Labstag\Form\Admin\GameType;
use Labstag\Message\AddGameMessage;
use Labstag\Message\ImportMessage;
use Labstag\Service\FileService;
use Labstag\Service\Igdb\GameService;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Translation\TranslatableMessage;
use Symfony\Contracts\Translation\TranslatorInterface;

class GameCrudController extends CrudControllerAbstract
{
    public function addAnotherPlatform(AdminContext $adminContext, TranslatorInterface $translator): JsonResponse
    {
        $request            = $adminContext->getRequest();
        $entityId           = $request->query->get('entityId');
        $repositoryAbstract = $this->getRepository();
        $game               = $repositoryAbstract->find($entityId);
        if (!$game instanceof Game) {
            return new JsonResponse(
                [
                    'status'  => 'error',
                    'message' => $translator->trans(new TranslatableMessage('Game not found')),
                ]
            );
        }

        $post = $request->request->all();
        if (!isset($post['game_other_platform'])) {
            return new JsonResponse(
                [
                    'status'  => 'error',
                    'message' => $translator->trans(new TranslatableMessage('No data found')),
                ]
            );
        }

        if (!isset($post['game_other_platform']['platforms'])) {
            return new JsonResponse(
                [
                    'status'  => 'error',
                    'message' => $translator->trans(new TranslatableMessage('No platform selected')),
                ]
            );
        }

        $platforms          = $post['game_other_platform']['platforms'];
        $platformRepository = $this->getRepository(Platform::class);
        foreach ($platforms as $platform) {
            $platformEntity = $platformRepository->find($platform);
            if ($platformEntity instanceof Platform) {
                $game->addPlatform($platformEntity);
            }
        }

        $repositoryAbstract->save($game);

        return new JsonResponse(
            [
                'status'  => 'success',
                'message' => $translator->trans(new TranslatableMessage('Platforms added successfully')),
            ]
        );
    }

    public function addByApi(
        AdminContext $adminContext,
        MessageBusInterface $messageBus,
        TranslatorInterface $translator,
    ): JsonResponse
    {
        $request  = $adminContext->getRequest();
        $id       = $request->query->get('id');
        $platform = $request->query->get('platform', '');
        $messageBus->dispatch(new AddGameMessage($id, 'game', $platform));

        return new JsonResponse(
            [
                'status'  => 'success',
                'id'      => $id,
                'message' => $translator->trans(new TranslatableMessage('Game is being added')),
            ]
        );
    }

    public function addToAnotherPlatform(AdminContext $adminContext): Response
    {
        $request                = $adminContext->getRequest();
        $entityId               = $request->query->get('entityId');
        $repositoryAbstract     = $this->getRepository();
        $platformRepository     = $this->getRepository(Platform::class);
        $game                   = $repositoryAbstract->find($entityId);
        $platforms              = $platformRepository->notInGame($game);

        $form    = $this->createForm(
            type: GameOtherPlatformType::class,
            options: ['platforms' => $platforms]
        );
        $form->handleRequest($request);

        return $this->render(
            'admin/game/other_platforms.html.twig',
            [
                'game' => $game,
                'ea'   => $adminContext,
                'form' => $form->createView(),
            ]
        );
    }

    public function apiGame(AdminContext $adminContext, GameService $gameService): Response
    {
        $request = $adminContext->getRequest();
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
        $games   = $gameService->getGameApi($data, $limit, $offset);

        return $this->render(
            'admin/api/game/list.html.twig',
            [
                'page'       => $page,
                'platform'   => $all['game']['platform'] ?? '',
                'controller' => self::class,
                'ea'         => $adminContext,
                'games'      => $games,
            ]
        );
    }

    #[\Override]
    public function configureActions(Actions $actions): Actions
    {
        $this->actionsFactory->init($actions, self::getEntityFqcn(), static::class);
        $this->actionsFactory->remove(Crud::PAGE_INDEX, Action::NEW);
        $this->setLinkIgdb();
        $this->addActionNewGame();
        $this->addActionImportGame();

        return $this->actionsFactory->show();
    }

    #[\Override]
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

    #[\Override]
    public function configureFields(string $pageName): iterable
    {
        $this->crudFieldFactory->setTabPrincipal($this->getContext());

        $textField = TextField::new('igdb', new TranslatableMessage('Igdb'));
        $textField->hideOnIndex();

        $associationField = AssociationField::new('platforms', new TranslatableMessage('Platforms'));
        $associationField->setTemplatePath('admin/field/game-platforms.html.twig');

        $this->crudFieldFactory->setTabDate($pageName);
        $this->crudFieldFactory->addFieldsToTab(
            'principal',
            [
                $this->crudFieldFactory->slugField(),
                $this->crudFieldFactory->titleField(),
                $this->crudFieldFactory->imageField('img', $pageName, self::getEntityFqcn()),
                DateField::new('releaseDate', new TranslatableMessage('Release date')),
                $textField,
                $associationField,
                $this->crudFieldFactory->categoriesFieldForPage(self::getEntityFqcn(), $pageName),
            ]
        );

        yield from $this->crudFieldFactory->getConfigureFields($pageName);
    }

    public static function getEntityFqcn(): string
    {
        return Game::class;
    }

    public function igdb(AdminContext $adminContext): Response
    {
        $entityId = $adminContext->getRequest()->query->get('entityId');
        $repositoryAbstract              = $this->getRepository();
        $game                            = $repositoryAbstract->find($entityId);

        $url = $game->getUrl();
        if (empty($url)) {
            return $this->redirectToRoute('admin_game_index');
        }

        return $this->redirect($url);
    }

    public function importFile(
        AdminContext $adminContext,
        MessageBusInterface $messageBus,
        FileService $fileService,
    ): JsonResponse
    {
        $request = $adminContext->getRequest();
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

        $content = file_get_contents($file->getPathname());
        $fileService->saveFileInAdapter('private', $file->getClientOriginalName() . '-' . time(), $content);

        $messageBus->dispatch(new ImportMessage($file->getClientOriginalName() . '-' . time(), 'game', $data));

        return new JsonResponse(
            [
                'status'  => 'success',
                'message' => 'Import started',
            ]
        );
    }

    public function setLinkIgdb(): void
    {
        if (!$this->actionsFactory->isTrash()) {
            return;
        }

        $action = Action::new('igdb', new TranslatableMessage('IGDB Page'));
        $action->setHtmlAttributes(
            ['target' => '_blank']
        );
        $action->linkToCrudAction('igdb');
        $action->displayIf(static fn ($entity): bool => is_null($entity->getDeletedAt()));

        $this->actionsFactory->add(Crud::PAGE_DETAIL, $action);
        $this->actionsFactory->add(Crud::PAGE_EDIT, $action);
        $this->actionsFactory->add(Crud::PAGE_INDEX, $action);

        $action = Action::new('addToAnotherPlatform', new TranslatableMessage('Add to another platform'));
        $action->linkToCrudAction('addToAnotherPlatform');
        $action->setHtmlAttributes(
            ['data-action' => 'show-modal']
        );

        $this->actionsFactory->add(Crud::PAGE_DETAIL, $action);
        $this->actionsFactory->add(Crud::PAGE_EDIT, $action);
        $this->actionsFactory->add(Crud::PAGE_INDEX, $action);
    }

    public function showModalGame(AdminContext $adminContext): Response
    {
        $request = $adminContext->getRequest();
        $form    = $this->createForm(GameType::class);
        $form->handleRequest($request);

        return $this->render(
            'admin/game/new.html.twig',
            [
                'controller' => self::class,
                'ea'         => $adminContext,
                'form'       => $form->createView(),
            ]
        );
    }

    public function showModalImportGame(AdminContext $adminContext): Response
    {
        $request = $adminContext->getRequest();
        $form    = $this->createForm(GameImportType::class);
        $form->handleRequest($request);

        return $this->render(
            'admin/game/import.html.twig',
            [
                'controller' => self::class,
                'ea'         => $adminContext,
                'form'       => $form->createView(),
            ]
        );
    }

    private function addActionImportGame(): void
    {
        if (!$this->actionsFactory->isTrash()) {
            return;
        }

        $action = Action::new('showModalImportGame', new TranslatableMessage('Import'));
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

        $action = Action::new('showModalGame', new TranslatableMessage('New game'));
        $action->linkToCrudAction('showModalGame');
        $action->setHtmlAttributes(
            ['data-action' => 'show-modal']
        );
        $action->createAsGlobalAction();

        $this->actionsFactory->add(Crud::PAGE_INDEX, $action);
    }
}
