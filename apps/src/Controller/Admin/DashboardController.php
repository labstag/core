<?php

namespace Labstag\Controller\Admin;

use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminDashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Dashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem;
use EasyCorp\Bundle\EasyAdminBundle\Config\UserMenu;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractDashboardController;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use Exception;
use Labstag\Controller\Admin\Factory\MenuItemFactory;
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
use Symfony\Component\DependencyInjection\Attribute\AutowireIterator;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Translation\TranslatableMessage;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

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
        protected TranslatorInterface $translator,
        protected CacheInterface $cache,
        #[AutowireIterator('labstag.admincontroller')]
        protected iterable $crudControllers,
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
        yield MenuItem::linkTo(
            NotificationCrudController::class,
            new TranslatableMessage(NotificationCrudController::getEntityLabel()),
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

        // Template management (kept separate for clarity)
        yield $this->menuItemFactory->createContentSubMenu(
            'template',
            new TranslatableMessage(TemplateCrudController::getEntityLabel()),
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

        $statLabels      = [];
        $statControllers = [];
        foreach ($this->crudControllers as $controller) {
            $label = $controller::getEntityLabel();
            if (str_contains($label, 'Tag') || str_contains($label, 'Category')) {
                continue;
            }

            $statLabels[]            = $label;
            $statControllers[$label] = $controller::class;
        }

        return $this->render(
            'admin/dashboard.html.twig',
            [
                'paragraphs'      => $paragraphs,
                'memos'           => $memos,
                'statLabels'      => $statLabels,
                'statControllers' => $statControllers,
            ]
        );
    }

    #[Route('/admin/stat/{entity}', name: 'admin_dashboard_stat')]
    public function stat(string $entity): JsonResponse
    {
        $entity = strtolower($entity);
        foreach ($this->crudControllers as $controller) {
            $label = $controller::getEntityLabel();
            if (strtolower($label) !== $entity) {
                continue;
            }

            $fqcn         = $controller::getEntityFqcn();
            $urlGenerator = $this->container->get(AdminUrlGenerator::class);
            $url          = $urlGenerator->setController($controller::class)->setAction('index')->generateUrl();

            $trashCount = 0;
            $trashUrl   = null;
            if (method_exists($this->entityManager->getRepository($fqcn), 'findDeleted')) {
                $trashItems = $this->trashStatsService->getTrashItems(
                    [
                        $label => $controller::class,
                    ],
                    $this->entityManager,
                    $urlGenerator
                );
                if ([] !== $trashItems) {
                    $trashCount = $trashItems[0]['count'];
                    $trashUrl   = $trashItems[0]['url'];
                }
            }

            return new JsonResponse(
                [
                    'label'      => $this->translator->trans($label),
                    'count'      => $this->entityManager->getRepository($fqcn)->count([]),
                    'url'        => $url,
                    'trashCount' => $trashCount,
                    'trashUrl'   => $trashUrl,
                ]
            );
        }

        return new JsonResponse(
            ['error' => 'Not found'],
            Response::HTTP_NOT_FOUND
        );
    }

    #[Route('/admin/stats', name: 'admin_dashboard_stats')]
    public function stats(): JsonResponse
    {
        $entities = [];
        foreach ($this->crudControllers as $controller) {
            $fqcn             = $controller::getEntityFqcn();
            $label            = $controller::getEntityLabel();
            $entities[$label] = [
                $fqcn,
                $controller::class,
            ];
        }

        $filteredEntities = array_filter($entities, fn ($label): bool => !str_contains($label, 'Tag') && !str_contains($label, 'Category'), ARRAY_FILTER_USE_KEY);
        $controllers      = array_combine(array_keys($filteredEntities), array_column($filteredEntities, 1));

        $urlGenerator = $this->container->get(AdminUrlGenerator::class);
        $trashItems   = $this->trashStatsService->getTrashItems(
            $controllers,
            $this->entityManager,
            $urlGenerator
        );
        $trashMap = [];
        foreach ($trashItems as $item) {
            $trashMap[$item['label']] = $item;
        }

        $stats = [];
        foreach ($filteredEntities as $label => [$entityFqcn, $controllerFqcn]) {
            $trash   = $trashMap[$label] ?? null;
            $stats[] = [
                'label'      => $this->translator->trans($label),
                'count'      => $this->entityManager->getRepository($entityFqcn)->count([]),
                'url'        => $urlGenerator->setController($controllerFqcn)->setAction('index')->generateUrl(),
                'trashCount' => $trash['count'] ?? 0,
                'trashUrl'   => $trash['url'] ?? null,
            ];
        }

        return new JsonResponse(
            ['stats' => $stats]
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
