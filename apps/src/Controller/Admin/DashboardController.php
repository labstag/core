<?php

namespace Labstag\Controller\Admin;

use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminDashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Dashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem;
use EasyCorp\Bundle\EasyAdminBundle\Config\UserMenu;
use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractDashboardController;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use Exception;
use Labstag\Api\IgdbApi;
use Labstag\Controller\Admin\Factory\MenuItemFactory;
use Labstag\Entity\Memo;
use Labstag\Entity\User;
use Labstag\Repository\ConfigurationRepository;
use Labstag\Repository\RepositoryAbstract;
use Labstag\Service\ConfigurationService;
use Labstag\Service\FileService;
use Labstag\Service\Igdb\GameService;
use Labstag\Service\Igdb\PlatformService;
use Labstag\Service\ParagraphService;
use Labstag\Service\SiteService;
use Labstag\Service\UserService;
use Labstag\Service\WorkflowService;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Translation\TranslatableMessage;

#[AdminDashboard(routePath: '/admin', routeName: 'admin')]
class DashboardController extends AbstractDashboardController
{
    public function __construct(
        protected EntityManagerInterface $entityManager,
        protected ParagraphService $paragraphService,
        protected ConfigurationService $configurationService,
        protected ConfigurationRepository $configurationRepository,
        protected UserService $userService,
        protected FileService $fileService,
        protected WorkflowService $workflowService,
        protected SiteService $siteService,
        protected MenuItemFactory $menuItemFactory,
    )
    {
    }

    #[Route(
        '/api/game/find/games',
        name: 'admin_api_game_find_games',
        defaults: ['_locale' => 'fr']
    )]
    public function apiGameFindGames(
        AdminContext $adminContext,
        GameService $gameService,
    ): Response
    {
        $request            = $adminContext->getRequest();
        $games              = $gameService->getGameApi($request);

        return $this->render(
            'admin/api/game/game.html.twig',
            [
                'platform'   => isset($platform) ? $platform->getId() : '',
                'controller' => GameCrudController::class,
                'ea'         => $adminContext,
                'games'      => $games,
            ]
        );
    }

    #[Route(
        '/api/game/find/platforms',
        name: 'admin_api_game_find_platforms',
        defaults: ['_locale' => 'fr']
    )]
    public function apiGameFindPlatforms(AdminContext $adminContext, PlatformService $platformService): Response
    {
        $request            = $adminContext->getRequest();

        $platforms = $platformService->getPlatformApi($request);

        return $this->render(
            'admin/api/game/platform.html.twig',
            [
                'controller' => PlatformCrudController::class,
                'ea'         => $adminContext,
                'platforms'  => $platforms,
            ]
        );
    }

    #[\Override]
    public function configureCrud(): Crud
    {
        return Crud::new()->setFormThemes(['admin/form.html.twig', '@EasyAdmin/crud/form_theme.html.twig']);
    }

    #[\Override]
    public function configureDashboard(): Dashboard
    {
        $data      = $this->configurationService->getConfiguration();
        $dashboard = Dashboard::new();
        $dashboard->setTitle($data->getName());
        $dashboard->setTranslationDomain('messages');
        $dashboard->renderContentMaximized();
        $dashboard->setLocales($this->userService->getLanguages());

        return $dashboard;
    }

    #[\Override]
    public function configureMenuItems(): iterable
    {
        $categories = $this->menuItemFactory->createCategoryMenuItems();
        $tags       = $this->menuItemFactory->createTagMenuItems();
        // Dashboard home
        yield MenuItem::linkToDashboard(new TranslatableMessage('Dashboard'), 'fa fa-home');

        // Shared taxonomy items (categories / tags) used in several content sub-menus
        $fieldsTAbs = [
            $this->buildContentMenus($categories, $tags),
            $this->buildSimpleCrudMenus(),
        ];
        foreach ($fieldsTAbs as $fieldTAb) {
            yield from $fieldTAb;
        }

        // Configuration (single editable entity)
        $configMenu = $this->buildConfigurationMenuItem();
        if (null !== $configMenu) {
            yield $configMenu;
        }

        // Template management (kept separate for clarity)
        yield $this->menuItemFactory->createContentSubMenu(
            'template',
            new TranslatableMessage('Templates'),
            'fas fa-code',
            TemplateCrudController::class
        );

        // Utility links
        yield from $this->buildUtilityMenus();
    }

    #[\Override]
    public function configureUserMenu(UserInterface $user): UserMenu
    {
        $userMenu = parent::configureUserMenu($user);
        if (!$user instanceof User) {
            return $userMenu;
        }

        $generator = $this->container->get(AdminUrlGenerator::class);
        $generator->setEntityId($user->getId());

        $userMenu->addMenuItems(
            [
                MenuItem::linkToUrl(
                    new TranslatableMessage('My profile'),
                    'fa fa-user',
                    $this->generateUrl(
                        'admin_profil_edit',
                        [
                            'entityId' => $user->getId(),
                        ]
                    )
                ),
            ]
        );
        $avatar = $user->getAvatar();
        if ('' != $avatar) {
            $basePath = $this->fileService->getBasePath($user, 'avatarFile');
            $userMenu->setAvatarUrl($basePath . '/' . $avatar);

            return $userMenu;
        }

        $userMenu->setGravatarEmail($user->getEmail());

        return $userMenu;
    }

    #[\Override]
    public function index(): Response
    {
        $repositoryAbstract = $this->getRepository(Memo::class);
        $memos              = $repositoryAbstract->findBy(
            ['enable' => true]
        );
        foreach ($memos as $memo) {
            $idMemo     = $memo->getId();
            $paragraphs = $memo->getParagraphs()->getValues();
            $paragraphs[$idMemo] = $this->paragraphService->generate($paragraphs, [], false);
        }

        return $this->render(
            'admin/dashboard.html.twig',
            [
                'paragraphs' => $paragraphs,
                'memos'      => $memos,
            ]
        );
    }

    protected function adminEmpty(string $entity): void
    {
        $repositoryAbstract              = $this->getRepository($entity);
        $all                             = $repositoryAbstract->findDeleted();
        foreach ($all as $row) {
            $repositoryAbstract->remove($row);
        }

        $repositoryAbstract->flush();
    }

    protected function adminRestore(string $entity, mixed $uuid): void
    {
        $repositoryAbstract              = $this->getRepository($entity);
        $data                            = $repositoryAbstract->find($uuid);
        if (is_null($data)) {
            throw new Exception(new TranslatableMessage('Data not found'));
        }

        if (!method_exists($data, 'isDeleted')) {
            throw new Exception(new TranslatableMessage('Method not found'));
        }

        if ($data->isDeleted()) {
            $data->setDeletedAt(null);
            $this->entityManager->persist($data);
            $this->entityManager->flush();
        }
    }

    /**
     * @return RepositoryAbstract<object>
     */
    protected function getRepository(string $entity): object
    {
        $entityRepository = $this->entityManager->getRepository($entity);
        if (is_null($entityRepository)) {
            throw new Exception('Repository not found');
        }

        return $entityRepository;
    }

    /**
     * Create configuration edit menu item if the configuration entity exists.
     */
    /**
     * Returns the configuration edit menu item when configuration exists.
     * Using object|null to support EasyAdmin specific UrlMenuItem implementation.
     */
    private function buildConfigurationMenuItem(): ?object
    {
        $configurations = $this->configurationRepository->findAll();
        $configuration  = $configurations[0] ?? null;
        if (!$configuration) {
            return null;
        }

        $generator = $this->container->get(AdminUrlGenerator::class);
        $generator->setAction(Action::EDIT);
        $generator->setController(ConfigurationCrudController::class);
        $generator->setEntityId($configuration->getId());

        $linkToUrl = MenuItem::linkToUrl(new TranslatableMessage('Options'), 'fas fa-cog', $generator->generateUrl());
        $linkToUrl->setPermission('ROLE_SUPER_ADMIN');

        return $linkToUrl;
    }

    /**
     * Build content (sub) menus that share a common pattern.
     *
     * @param array<string, mixed> $categories
     * @param array<string, mixed> $tags
     *
     * @return iterable<MenuItem>
     */
    private function buildContentMenus(array $categories, array $tags): iterable
    {
        // Definition: identifier, label, icon, controller, categories?, tags?, extra children
        $definitions = [
            [
                'story',
                new TranslatableMessage('Story'),
                'fas fa-landmark',
                StoryCrudController::class,
                $categories,
                $tags,
                [],
                false,
            ],
            [
                'chapter',
                new TranslatableMessage('Chapter'),
                'fas fa-landmark',
                ChapterCrudController::class,
                null,
                null,
                [],
                false,
            ],
            [
                'movie',
                new TranslatableMessage('Movie'),
                'fas fa-film',
                MovieCrudController::class,
                $categories,
                null,
                [
                    MenuItem::linkToCrud(
                        new TranslatableMessage('Sagas'),
                        'fas fa-video',
                        SagaCrudController::getEntityFqcn()
                    ),
                ],
                false,
            ],
            [
                'serie',
                new TranslatableMessage('Serie'),
                'fas fa-film',
                SerieCrudController::class,
                $categories,
                null,
                [
                    MenuItem::linkToCrud(
                        new TranslatableMessage('Season'),
                        'fas fa-video',
                        SeasonCrudController::getEntityFqcn()
                    ),
                    MenuItem::linkToCrud(
                        new TranslatableMessage('Episode'),
                        'fas fa-video',
                        EpisodeCrudController::getEntityFqcn()
                    ),
                ],
                false,
            ],
            [
                'game',
                new TranslatableMessage('Game'),
                'fas fa-gamepad',
                GameCrudController::class,
                $categories,
                null,
                [
                    MenuItem::linkToCrud(
                        new TranslatableMessage('Platform'),
                        'fas fa-desktop',
                        PlatformCrudController::getEntityFqcn()
                    ),
                    MenuItem::linkToCrud(
                        new TranslatableMessage('Franchise'),
                        'fas fa-th-large',
                        FranchiseCrudController::getEntityFqcn()
                    ),
                ],
                true,
            ],
            [
                'page',
                new TranslatableMessage('Page'),
                'fas fa-columns',
                PageCrudController::class,
                $categories,
                $tags,
                [],
                false,
            ],
            [
                'post',
                new TranslatableMessage('Post'),
                'fas fa-newspaper',
                PostCrudController::class,
                $categories,
                $tags,
                [],
                false,
            ],
        ];

        foreach ($definitions as [$code, $label, $icon, $controller, $cats, $tgs, $children, $disableAdd]) {
            yield $this->menuItemFactory->createContentSubMenu(
                $code,
                $label,
                $icon,
                $controller,
                $disableAdd,
                $cats,
                $tgs,
                $children
            );
        }
    }

    /**
     * Simple CRUD links sharing the same creation pattern.
     */
    /**
     * @return iterable<MenuItem>
     */
    private function buildSimpleCrudMenus(): iterable
    {
        $items = [
            [
                new TranslatableMessage('Recommendations'),
                'fas fa-comment-medical',
                RecommendationCrudController::getEntityFqcn(),
            ],
            [
                new TranslatableMessage('Company'),
                'fas fa-building',
                CompanyCrudController::getEntityFqcn(),
            ],
            [
                new TranslatableMessage('Edito'),
                'fas fa-info',
                EditoCrudController::getEntityFqcn(),
            ],
            [
                new TranslatableMessage('Memo'),
                'fas fa-memory',
                MemoCrudController::getEntityFqcn(),
            ],
            [
                new TranslatableMessage('Media'),
                'fas fa-photo-video',
                MediaCrudController::getEntityFqcn(),
            ],
            [
                new TranslatableMessage('Meta'),
                'fa fa-file-alt',
                MetaCrudController::getEntityFqcn(),
            ],
            [
                new TranslatableMessage('Paragraph'),
                'fa fa-paragraph',
                ParagraphCrudController::getEntityFqcn(),
            ],
            [
                new TranslatableMessage('Block'),
                'fa fa-cubes',
                BlockCrudController::getEntityFqcn(),
            ],
            [
                new TranslatableMessage('Geocode'),
                'fas fa-map-signs',
                GeoCodeCrudController::getEntityFqcn(),
            ],
            [
                new TranslatableMessage('Star'),
                'fas fa-star',
                StarCrudController::getEntityFqcn(),
            ],
            [
                new TranslatableMessage('User'),
                'fa fa-user',
                UserCrudController::getEntityFqcn(),
            ],
            [
                new TranslatableMessage('Group'),
                'fa fa-users',
                GroupCrudController::getEntityFqcn(),
            ],
            [
                new TranslatableMessage('Ban IP'),
                'fas fa-ban',
                BanIpCrudController::getEntityFqcn(),
            ],
            [
                new TranslatableMessage('Redirection'),
                'fas fa-directions',
                RedirectionCrudController::getEntityFqcn(),
            ],
            [
                new TranslatableMessage('Http error Logs'),
                'fas fa-clipboard-list',
                HttpErrorLogsCrudController::getEntityFqcn(),
            ],
            [
                new TranslatableMessage('Submission'),
                'fas fa-clipboard-list',
                SubmissionCrudController::getEntityFqcn(),
            ],
        ];

        foreach ($items as [$label, $icon, $fqcn]) {
            yield MenuItem::linkToCrud($label, $icon, $fqcn);
        }

        yield MenuItem::linkToRoute(
            new TranslatableMessage('Permissions'),
            'fa fa-user-shield',
            'admin_permission'
        );
    }

    /**
     * Utility / maintenance links.
     *
     * @return iterable<MenuItem>
     */
    private function buildUtilityMenus(): iterable
    {
        yield MenuItem::linkToUrl(
            new TranslatableMessage('Clear Cache'),
            'fas fa-trash',
            $this->generateUrl('admin_cacheclear')
        );

        yield MenuItem::linkToUrl(
            new TranslatableMessage('View Site'),
            'fas fa-laptop-house',
            $this->generateUrl('front')
        )->setLinkTarget('_blank');
    }
}
