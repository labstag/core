<?php

namespace Labstag\Controller\Admin;

use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminDashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Dashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem;
use EasyCorp\Bundle\EasyAdminBundle\Config\UserMenu;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractDashboardController;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use Exception;
use Labstag\Controller\Admin\Factory\MenuItemFactory;
use Labstag\Entity\User;
use Labstag\Repository\Abstract\ServiceEntityRepositoryLib;
use Labstag\Repository\ConfigurationRepository;
use Labstag\Service\ConfigurationService;
use Labstag\Service\FileService;
use Labstag\Service\SiteService;
use Labstag\Service\UserService;
use Labstag\Service\WorkflowService;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Translation\TranslatableMessage;

#[AdminDashboard(routePath: '/admin', routeName: 'admin')]
class DashboardController extends AbstractDashboardController
{
    public function __construct(
        protected EntityManagerInterface $entityManager,
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

    #[\Override]
    public function configureDashboard(): Dashboard
    {
        $data      = $this->configurationService->getConfiguration();
        $dashboard = Dashboard::new();
        $dashboard->setTitle($data->getName());
        $dashboard->setTranslationDomain('admin');
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
            'Templates',
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
        return $this->render('admin/dashboard.html.twig', []);
    }

    protected function adminEmpty(string $entity): void
    {
        $serviceEntityRepositoryLib = $this->getRepository($entity);
        $all                        = $serviceEntityRepositoryLib->findDeleted();
        foreach ($all as $row) {
            $serviceEntityRepositoryLib->remove($row);
        }

        $serviceEntityRepositoryLib->flush();
    }

    protected function adminRestore(string $entity, mixed $uuid): void
    {
        $serviceEntityRepositoryLib = $this->getRepository($entity);
        $data                       = $serviceEntityRepositoryLib->find($uuid);
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

    protected function getRepository(string $entity): ServiceEntityRepositoryLib
    {
        $entityRepository = $this->entityManager->getRepository($entity);
        if (!$entityRepository instanceof ServiceEntityRepositoryLib) {
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

        return MenuItem::linkToUrl(new TranslatableMessage('Options'), 'fas fa-cog', $generator->generateUrl());
    }

    /**
     * Build content (sub) menus that share a common pattern.
     */
    private function buildContentMenus(array $categories, array $tags): iterable
    {
        // Definition: identifier, label, icon, controller, categories?, tags?, extra children
        $definitions = [
            [
                'story',
                'Story',
                'fas fa-landmark',
                StoryCrudController::class,
                $categories,
                $tags,
                [],
            ],
            [
                'chapter',
                'Chapter',
                'fas fa-landmark',
                ChapterCrudController::class,
                null,
                $tags,
                [],
            ],
            [
                'movie',
                'Movie',
                'fas fa-film',
                MovieCrudController::class,
                $categories,
                $tags,
                [
                    MenuItem::linkToCrud(
                        new TranslatableMessage('Sagas'),
                        'fas fa-video',
                        SagaCrudController::getEntityFqcn()
                    ),
                ],
            ],
            [
                'page',
                'Page',
                'fas fa-columns',
                PageCrudController::class,
                $categories,
                $tags,
                [],
            ],
            [
                'post',
                'Post',
                'fas fa-newspaper',
                PostCrudController::class,
                $categories,
                $tags,
                [],
            ],
        ];

        foreach ($definitions as [$code, $label, $icon, $controller, $cats, $tgs, $children]) {
            yield $this->menuItemFactory->createContentSubMenu(
                $code,
                $label,
                $icon,
                $controller,
                $cats,
                $tgs,
                $children ?? []
            );
        }
    }

    /**
     * Simple CRUD links sharing the same creation pattern.
     */
    private function buildSimpleCrudMenus(): iterable
    {
        $items = [
            [
                'Edito',
                'fas fa-info',
                EditoCrudController::getEntityFqcn(),
            ],
            [
                'Memo',
                'fas fa-memory',
                MemoCrudController::getEntityFqcn(),
            ],
            [
                'Meta',
                'fa fa-file-alt',
                MetaCrudController::getEntityFqcn(),
            ],
            [
                'Paragraph',
                'fa fa-paragraph',
                ParagraphCrudController::getEntityFqcn(),
            ],
            [
                'Block',
                'fa fa-cubes',
                BlockCrudController::getEntityFqcn(),
            ],
            [
                'Geocode',
                'fas fa-map-signs',
                GeoCodeCrudController::getEntityFqcn(),
            ],
            [
                'Star',
                'fas fa-star',
                StarCrudController::getEntityFqcn(),
            ],
            [
                'User',
                'fa fa-user',
                UserCrudController::getEntityFqcn(),
            ],
            [
                'Ban IP',
                'fas fa-ban',
                BanIpCrudController::getEntityFqcn(),
            ],
            [
                'Redirection',
                'fas fa-directions',
                RedirectionCrudController::getEntityFqcn(),
            ],
            [
                'Http error Logs',
                'fas fa-clipboard-list',
                HttpErrorLogsCrudController::getEntityFqcn(),
            ],
            [
                'Submission',
                'fas fa-clipboard-list',
                SubmissionCrudController::getEntityFqcn(),
            ],
        ];

        foreach ($items as [$label, $icon, $fqcn]) {
            yield MenuItem::linkToCrud(new TranslatableMessage($label), $icon, $fqcn);
        }
    }

    /**
     * Utility / maintenance links.
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
