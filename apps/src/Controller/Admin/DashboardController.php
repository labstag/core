<?php

namespace Labstag\Controller\Admin;

use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Dashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\Menu\CrudMenuItem;
use EasyCorp\Bundle\EasyAdminBundle\Config\Menu\SubMenuItem;
use EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem;
use EasyCorp\Bundle\EasyAdminBundle\Config\UserMenu;
use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractDashboardController;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use Exception;
use Labstag\Entity\User;
use Labstag\Lib\ServiceEntityRepositoryLib;
use Labstag\Repository\ConfigurationRepository;
use Labstag\Service\FileService;
use Labstag\Service\SiteService;
use Labstag\Service\UserService;
use Labstag\Service\WorkflowService;
use Override;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Translation\TranslatableMessage;

class DashboardController extends AbstractDashboardController
{
    public function __construct(
        protected EntityManagerInterface $entityManager,
        protected ConfigurationRepository $configurationRepository,
        protected UserService $userService,
        protected FileService $fileService,
        protected WorkflowService $workflowService,
        protected SiteService $siteService,
    )
    {
    }

    #[Override]
    public function configureDashboard(): Dashboard
    {
        $data = $this->siteService->getConfiguration();
        $dashboard = Dashboard::new();
        $dashboard->setTitle($data->getName());
        $dashboard->setTranslationDomain('admin');
        $dashboard->renderContentMaximized();
        $dashboard->setLocales($this->userService->getLanguages());

        return $dashboard;
    }

    /**
     * @param CrudMenuItem[] $categories
     * @param CrudMenuItem[] $tags
     */
    private function configureMenuItemsStory(array $categories, array $tags): SubMenuItem
    {
        return MenuItem::subMenu(new TranslatableMessage('Story'))->setSubItems(
            [
                MenuItem::linkToCrud(
                    new TranslatableMessage('List'),
                    'fa fa-list',
                    StoryCrudController::getEntityFqcn()
                ),
                MenuItem::linkToCrud(
                    new TranslatableMessage('New'),
                    'fas fa-plus',
                    StoryCrudController::getEntityFqcn()
                )->setAction(Action::NEW),
                $categories['story'],
                $tags['story'],
            ]
        );
    }

    /**
     * @param CrudMenuItem[] $tags
     */
    private function configureMenuItemsChapter(array $tags): SubMenuItem
    {
        return MenuItem::subMenu(new TranslatableMessage('Chapter'))->setSubItems(
            [
                MenuItem::linkToCrud(
                    new TranslatableMessage('List'),
                    'fa fa-list',
                    ChapterCrudController::getEntityFqcn()
                ),
                MenuItem::linkToCrud(
                    new TranslatableMessage('New'),
                    'fas fa-plus',
                    ChapterCrudController::getEntityFqcn()
                )->setAction(Action::NEW),
                $tags['chapter'],
            ]
        );
    }

    /**
     * @param CrudMenuItem[] $categories
     * @param CrudMenuItem[] $tags
     */
    private function configureMenuItemsPage(array $categories, array $tags): SubMenuItem
    {
        return MenuItem::subMenu(new TranslatableMessage('Page'), 'fas fa-columns')->setSubItems(
            [
                MenuItem::linkToCrud(
                    new TranslatableMessage('List'),
                    'fa fa-list',
                    PageCrudController::getEntityFqcn()
                ),
                MenuItem::linkToCrud(
                    new TranslatableMessage('New'),
                    'fas fa-plus',
                    PageCrudController::getEntityFqcn()
                )->setAction(Action::NEW),
                $categories['page'],
                $tags['page'],
            ]
        );
    }

    /**
     * @param CrudMenuItem[] $categories
     * @param CrudMenuItem[] $tags
     */
    private function configureMenuItemsPost(array $categories, array $tags): SubMenuItem
    {
        return MenuItem::subMenu(new TranslatableMessage('Post'), 'fas fa-newspaper')->setSubItems(
            [
                MenuItem::linkToCrud(
                    new TranslatableMessage('List'),
                    'fa fa-list',
                    PostCrudController::getEntityFqcn()
                ),
                MenuItem::linkToCrud(
                    new TranslatableMessage('New'),
                    'fas fa-plus',
                    PostCrudController::getEntityFqcn()
                )->setAction(Action::NEW),
                $categories['post'],
                $tags['post'],
            ]
        );
    }

    private function configureMenuItemsTemplate(): SubMenuItem
    {
        return MenuItem::subMenu(new TranslatableMessage('Templates'), 'fas fa-code')->setSubItems(
            [
                MenuItem::linkToCrud(
                    new TranslatableMessage('List'),
                    'fa fa-list',
                    TemplateCrudController::getEntityFqcn()
                ),
                MenuItem::linkToCrud(
                    new TranslatableMessage('New'),
                    'fas fa-plus',
                    TemplateCrudController::getEntityFqcn()
                )->setAction(Action::NEW),
            ]
        );
    }

    /**
     * @param CrudMenuItem[] $categories
     */
    private function configureMenuItemsMovie(array $categories): SubMenuItem
    {
        return MenuItem::subMenu(new TranslatableMessage('Movie'))->setSubItems(
            [
                MenuItem::linkToCrud(
                    new TranslatableMessage('List'),
                    'fa fa-list',
                    MovieCrudController::getEntityFqcn()
                ),
                MenuItem::linkToCrud(
                    new TranslatableMessage('New'),
                    'fas fa-plus',
                    MovieCrudController::getEntityFqcn()
                )->setAction(Action::NEW),
                $categories['movie'],
            ]
        );
    }

    #[Override]
    public function configureMenuItems(): iterable
    {
        yield MenuItem::linkToDashboard(new TranslatableMessage('Dashboard'), 'fa fa-home');
        $categories = $this->setCategories();
        $tags = $this->setTags();

        yield $this->configureMenuItemsStory($categories, $tags);
        yield $this->configureMenuItemsChapter($tags);
        yield $this->configureMenuItemsMovie($categories);

        yield MenuItem::linkToCrud(
            new TranslatableMessage('Edito'),
            'fas fa-info',
            EditoCrudController::getEntityFqcn()
        );

        yield MenuItem::linkToCrud(
            new TranslatableMessage('Memo'),
            'fas fa-memory',
            MemoCrudController::getEntityFqcn()
        );

        yield $this->configureMenuItemsPage($categories, $tags);

        yield $this->configureMenuItemsPost($categories, $tags);

        yield MenuItem::linkToCrud(
            new TranslatableMessage('Meta'),
            'fa fa-file-alt',
            MetaCrudController::getEntityFqcn()
        );
        yield MenuItem::linkToCrud(
            new TranslatableMessage('Paragraph'),
            'fa fa-paragraph',
            ParagraphCrudController::getEntityFqcn()
        );
        yield MenuItem::linkToCrud(
            new TranslatableMessage('Block'),
            'fa fa-cubes',
            BlockCrudController::getEntityFqcn()
        );
        yield MenuItem::linkToCrud(
            new TranslatableMessage('Géocode'),
            'fas fa-map-signs',
            GeoCodeCrudController::getEntityFqcn()
        );
        yield MenuItem::linkToCrud(new TranslatableMessage('Star'), 'fas fa-star', StarCrudController::getEntityFqcn());
        yield MenuItem::linkToCrud(new TranslatableMessage('User'), 'fa fa-user', UserCrudController::getEntityFqcn());
        yield MenuItem::linkToCrud(
            new TranslatableMessage('Ban IP'),
            'fas fa-ban',
            BanIpCrudController::getEntityFqcn()
        );
        yield MenuItem::linkToCrud(
            new TranslatableMessage('Redirection'),
            'fas fa-directions',
            RedirectionCrudController::getEntityFqcn()
        );
        yield MenuItem::linkToCrud(
            new TranslatableMessage('Http error Logs'),
            'fas fa-clipboard-list',
            HttpErrorLogsCrudController::getEntityFqcn()
        );
        yield MenuItem::linkToCrud(
            new TranslatableMessage('Submission'),
            'fas fa-clipboard-list',
            SubmissionCrudController::getEntityFqcn()
        );

        $configuration = null;
        $configurations = $this->configurationRepository->findAll();
        $generator = $this->container->get(AdminUrlGenerator::class);
        $configuration = (count($configurations) != 0) ? $configurations[0] : null;
        if (is_null($configuration)) {
            return $this->redirectToRoute('admin');
        }

        $generator->setAction(Action::EDIT);
        $generator->setController(ConfigurationCrudController::class);
        $generator->setEntityId($configuration->getId());
        yield MenuItem::linkToUrl(
            new TranslatableMessage('Options'),
            'fas fa-cog',
            $generator->generateUrl()
        );

        yield $this->configureMenuItemsTemplate();
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
                    $this->generateUrl('admin_profil_edit', ['entityId' => $user->getId()])
                ),
            ]
        );
        $avatar = $user->getAvatar();
        if ($avatar != '') {
            $basePath = $this->fileService->getBasePath($user, 'avatarFile');
            $userMenu->setAvatarUrl($basePath . '/' . $avatar);

            return $userMenu;
        }

        $userMenu->setGravatarEmail($user->getEmail());

        return $userMenu;
    }

    #[Route(
        '/admin/{_locale}',
        name: 'admin',
        defaults: ['_locale' => 'fr']
    )]
    #[Override]
    public function index(): Response
    {
        return $this->render('admin/dashboard.html.twig', []);
    }

    protected function adminEmpty(string $entity): void
    {
        $serviceEntityRepositoryLib = $this->getRepository($entity);
        $all = $serviceEntityRepositoryLib->findDeleted();
        foreach ($all as $row) {
            $serviceEntityRepositoryLib->remove($row);
        }

        $serviceEntityRepositoryLib->flush();
    }

    protected function adminRestore(string $entity, mixed $uuid): void
    {
        $serviceEntityRepositoryLib = $this->getRepository($entity);
        $data = $serviceEntityRepositoryLib->find($uuid);
        if (is_null($data)) {
            throw new Exception(new TranslatableMessage('Data not found'));
        }

        $methods = get_class_methods($data);
        if (!in_array('isDeleted', $methods)) {
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
     * @return CrudMenuItem[]
     */
    private function setCategories(): array
    {
        $tab = [
            'story' => [
                'crud'       => StoryCategoryCrudController::getEntityFqcn(),
                'controller' => StoryCategoryCrudController::class,
            ],
            'page'  => [
                'crud'       => PageCategoryCrudController::getEntityFqcn(),
                'controller' => PageCategoryCrudController::class,
            ],
            'post'  => [
                'crud'       => PostCategoryCrudController::getEntityFqcn(),
                'controller' => PostCategoryCrudController::class,
            ],
            'movie' => [
                'crud'       => MovieCategoryCrudController::getEntityFqcn(),
                'controller' => MovieCategoryCrudController::class,
            ],
        ];
        $categories = [];
        foreach ($tab as $key => $data) {
            $categories[$key] = MenuItem::linkToCrud(
                new TranslatableMessage('Category'),
                'fas fa-hashtag',
                $data['crud']
            );
            $categories[$key]->setController($data['controller']);
        }

        return $categories;
    }

    /**
     * @return CrudMenuItem[]
     */
    private function setTags(): array
    {
        $tab = [
            'story'   => [
                'crud'       => StoryTagCrudController::getEntityFqcn(),
                'controller' => StoryTagCrudController::class,
            ],
            'chapter' => [
                'crud'       => ChapterTagCrudController::getEntityFqcn(),
                'controller' => ChapterTagCrudController::class,
            ],
            'page'    => [
                'crud'       => PageTagCrudController::getEntityFqcn(),
                'controller' => PageTagCrudController::class,
            ],
            'post'    => [
                'crud'       => PostTagCrudController::getEntityFqcn(),
                'controller' => PostTagCrudController::class,
            ],
        ];
        $tags = [];
        foreach ($tab as $key => $data) {
            $tags[$key] = MenuItem::linkToCrud(new TranslatableMessage('Tag'), 'fas fa-tags', $data['crud']);
            $tags[$key]->setController($data['controller']);
        }

        return $tags;
    }
}
