<?php

namespace Labstag\Controller\Admin;

use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminDashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Dashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem;
use EasyCorp\Bundle\EasyAdminBundle\Config\UserMenu;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractDashboardController;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use Exception;
use Labstag\Controller\Admin\Factory\MenuItemFactory;
use Labstag\Entity\Configuration;
use Labstag\Entity\Memo;
use Labstag\Entity\User;
use Labstag\Repository\ConfigurationRepository;
use Labstag\Repository\RepositoryAbstract;
use Labstag\Service\ConfigurationService;
use Labstag\Service\DashboardMenuService;
use Labstag\Service\FileService;
use Labstag\Service\ParagraphService;
use Labstag\Service\SiteService;
use Labstag\Service\TrashStatsService;
use Labstag\Service\UserService;
use Labstag\Service\WorkflowService;
use Override;
use Symfony\Component\HttpFoundation\Response;
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
        protected TrashStatsService $trashStatsService,
        protected DashboardMenuService $dashboardMenuService,
    )
    {
    }

    #[Override]
    public function configureCrud(): Crud
    {
        return Crud::new()->setFormThemes(['admin/form.html.twig', '@EasyAdmin/crud/form_theme.html.twig']);
    }

    #[Override]
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

    #[Override]
    public function configureMenuItems(): iterable
    {
        $categories = $this->menuItemFactory->createCategoryMenuItems();
        $tags       = $this->menuItemFactory->createTagMenuItems();
        // Dashboard home
        yield MenuItem::linkToDashboard(new TranslatableMessage('Dashboard'), 'fa fa-home');
        $translatableMessage = new TranslatableMessage('Notifications');
        yield MenuItem::linkTo(
            NotificationCrudController::class,
            $translatableMessage->getMessage(),
            'fas fa-bell'
        );

        // Shared taxonomy items (categories / tags) used in several content sub-menus
        $fieldsTAbs = [
            $this->dashboardMenuService->buildContentMenus($categories, $tags),
            $this->dashboardMenuService->buildSimpleCrudMenus(),
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
        yield from $this->dashboardMenuService->buildUtilityMenus();
    }

    #[Override]
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

    #[Override]
    public function index(): Response
    {
        $repositoryAbstract = $this->getRepository(Memo::class);
        $memos              = $repositoryAbstract->findBy(
            ['enable' => true]
        );

        $paragraphs = [];
        foreach ($memos as $memo) {
            $idMemo              = $memo->getId();
            $paragraphs          = $memo->getParagraphs()->getValues();
            $paragraphs[$idMemo] = $this->paragraphService->generate($paragraphs, [], false);
        }

        $controllers = [
            'Person'     => PersonCrudController::class,
            'Company'    => CompanyCrudController::class,
            'Edito'      => EditoCrudController::class,
            'Memo'       => MemoCrudController::class,
            'Media'      => MediaCrudController::class,
            'Meta'       => MetaCrudController::class,
            'Paragraphs' => ParagraphCrudController::class,
            'Block'      => BlockCrudController::class,
            'Star'       => StarCrudController::class,
            'User'       => UserCrudController::class,
            'Story'      => StoryCrudController::class,
            'Chapter'    => ChapterCrudController::class,
            'Movie'      => MovieCrudController::class,
            'Serie'      => SerieCrudController::class,
            'Season'     => SeasonCrudController::class,
            'Episode'    => EpisodeCrudController::class,
            'Game'       => GameCrudController::class,
            'Platform'   => PlatformCrudController::class,
            'Franchise'  => FranchiseCrudController::class,
            'Page'       => PageCrudController::class,
            'Post'       => PostCrudController::class,
        ];
        $trash = $this->trashStatsService->getTrashItems(
            $controllers,
            $this->entityManager,
            $this->container->get(AdminUrlGenerator::class)
        );

        return $this->render(
            'admin/dashboard.html.twig',
            [
                'paragraphs' => $paragraphs,
                'memos'      => $memos,
                'trash'      => $trash,
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
        $configurations = $this->getRepository(Configuration::class)->findAll();
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
    /**
     * Simple CRUD links sharing the same creation pattern.
     */
    /**
     * @return iterable<MenuItem>
     */
    /**
     * Utility / maintenance links.
     *
     * @return iterable<MenuItem>
     */
    /**
     * Menu listing all soft-deletable entities' trash views.
     */
}
