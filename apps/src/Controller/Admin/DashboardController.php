<?php

namespace Labstag\Controller\Admin;

use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Dashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem;
use EasyCorp\Bundle\EasyAdminBundle\Config\UserMenu;
use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractDashboardController;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use Exception;
use Labstag\Entity\Block;
use Labstag\Entity\Category;
use Labstag\Entity\Chapter;
use Labstag\Entity\Edito;
use Labstag\Entity\GeoCode;
use Labstag\Entity\Memo;
use Labstag\Entity\Meta;
use Labstag\Entity\Page;
use Labstag\Entity\Paragraph;
use Labstag\Entity\Post;
use Labstag\Entity\Star;
use Labstag\Entity\Story;
use Labstag\Entity\Tag;
use Labstag\Entity\User;
use Labstag\Repository\ConfigurationRepository;
use Labstag\Service\FileService;
use Labstag\Service\SiteService;
use Labstag\Service\UserService;
use Labstag\Service\WorkflowService;
use Override;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Translation\TranslatableMessage;

class DashboardController extends AbstractDashboardController
{
    public function __construct(
        protected EntityManagerInterface $entityManager,
        protected UserService $userService,
        protected FileService $fileService,
        protected WorkflowService $workflowService,
        protected SiteService $siteService
    )
    {
    }

    #[Route('/admin/{_locale}/blank', name: 'admin_blank')]
    public function blank(): Response
    {
        return $this->render(
            'admin/blank.html.twig',
            []
        );
    }

    #[Route('/admin/{_locale}/purge', name: 'admin_cacheclear')]
    public function cacheclear(KernelInterface $kernel): Response
    {
        $total = $this->fileService->deletedFileByEntities();
        if (0 != $total) {
            $this->addFlash('success', new TranslatableMessage('%total% file(s) deleted', ['%total%' => $total]));
        }

        //execution de la commande en console
        $application = new Application($kernel);
        $application->setAutoExit(false);

        $arrayInput = new ArrayInput(['cache:clear']);

        $bufferedOutput = new BufferedOutput();
        $application->run($arrayInput, $bufferedOutput);

        $this->addFlash('success', new TranslatableMessage('Cache cleared'));

        return $this->redirectToRoute('admin');
    }

    #[Route('/admin/{_locale}/config', name: 'admin_config')]
    public function config(ConfigurationRepository $configurationRepository)
    {
        $configuration  = null;
        $configurations = $configurationRepository->findAll();
        $generator      = $this->container->get(AdminUrlGenerator::class);
        $configuration  = (0 != count($configurations)) ? $configurations[0] : null;
        if (is_null($configuration)) {
            return $this->redirectToRoute('admin');
        }

        $generator->setAction(Action::EDIT);
        $generator->setController(ConfigurationCrudController::class);
        $generator->setEntityId($configuration->getId());

        $url = $generator->generateUrl();

        return $this->redirect($url);
    }

    #[Override]
    public function configureDashboard(): Dashboard
    {
        $data      = $this->siteService->getConfiguration();
        $dashboard = Dashboard::new();
        $dashboard->setTitle($data->getSiteName());
        $dashboard->setTranslationDomain('admin');
        $dashboard->renderContentMaximized();
        $dashboard->setLocales($this->userService->getLanguages());

        return $dashboard;
    }

    #[Override]
    public function configureMenuItems(): iterable
    {
        yield MenuItem::linkToDashboard('Dashboard', 'fa fa-home');
        $tab = [
            'story' => StoryCategoryCrudController::class,
            'page'  => PageCategoryCrudController::class,
            'post'  => PostCategoryCrudController::class,
        ];
        $categories = [];
        foreach ($tab as $key => $value) {
            $categories[$key] = MenuItem::linkToCrud('Category', 'fas fa-hashtag', Category::class);
            $categories[$key]->setController($value);
        }

        $tab = [
            'story'   => StoryTagCrudController::class,
            'chapter' => ChapterTagCrudController::class,
            'page'    => PageTagCrudController::class,
            'post'    => PostTagCrudController::class,
        ];
        $tags = [];
        foreach ($tab as $key => $value) {
            $tags[$key] = MenuItem::linkToCrud('Tag', 'fas fa-tags', Tag::class);
            $tags[$key]->setController($value);
        }

        yield MenuItem::subMenu('Story')->setSubItems(
            [
                MenuItem::linkToCrud('List', 'fa fa-list', Story::class),
                MenuItem::linkToCrud('new', 'fas fa-plus', Story::class)->setAction(Action::NEW),
                $categories['story'],
                $tags['story'],
            ]
        );
        yield MenuItem::subMenu('Chapter')->setSubItems(
            [
                MenuItem::linkToCrud('List', 'fa fa-list', Chapter::class),
                MenuItem::linkToCrud('new', 'fas fa-plus', Chapter::class)->setAction(Action::NEW),
                $tags['chapter'],
            ]
        );

        yield MenuItem::linkToCrud('Edito', 'fas fa-info', Edito::class);

        yield MenuItem::linkToCrud('Memo', 'fas fa-memory', Memo::class);

        yield MenuItem::subMenu('Page', 'fas fa-columns')->setSubItems(
            [
                MenuItem::linkToCrud('List', 'fa fa-list', Page::class),
                MenuItem::linkToCrud('new', 'fas fa-plus', Page::class)->setAction(Action::NEW),
                $categories['page'],
                $tags['page'],
            ]
        );

        yield MenuItem::subMenu('Post', 'fas fa-newspaper')->setSubItems(
            [
                MenuItem::linkToCrud('List', 'fa fa-list', Post::class),
                MenuItem::linkToCrud('new', 'fas fa-plus', Post::class)->setAction(Action::NEW),
                $categories['post'],
                $tags['post'],
            ]
        );

        yield MenuItem::linkToCrud('Meta', 'fa fa-file-alt', Meta::class);
        yield MenuItem::linkToCrud('Paragraph', 'fa fa-paragraph', Paragraph::class);
        yield MenuItem::linkToCrud('Block', 'fa fa-cubes', Block::class);
        yield MenuItem::linkToCrud('Géocode', 'fas fa-map-signs', GeoCode::class);
        yield MenuItem::linkToCrud('Star', 'fas fa-star', Star::class);
        yield MenuItem::linkToCrud('User', 'fa fa-user', User::class);
        yield MenuItem::linkToRoute('Options', 'fas fa-cog', 'admin_config');
        yield MenuItem::linkToRoute('Vider le cache', 'fas fa-trash', 'admin_cacheclear');
        yield MenuItem::linkToRoute('Voir le site', 'fas fa-laptop-house', 'front')->setLinkTarget('_blank');
    }

    #[Override]
    public function configureUserMenu(UserInterface $user): UserMenu
    {
        $userMenu = parent::configureUserMenu($user);
        if (!$user instanceof User) {
            return $userMenu;
        }

        $userMenu->addMenuItems(
            [
                MenuItem::linkToRoute('Mon profil', 'fa fa-user', 'admin_profil'),
            ]
        );
        $avatar = $user->getAvatar();
        if ('' != $avatar) {
            $basePath = $this->fileService->getBasePath($user, 'avatarFile');
            $userMenu->setAvatarUrl($basePath.'/'.$avatar);

            return $userMenu;
        }

        $userMenu->setGravatarEmail($user->getEmail());

        return $userMenu;
    }

    #[Route('/admin/{_locale}/restore', name: 'admin_restore')]
    #[Route('/admin/{_locale}/empty', name: 'admin_empty')]
    public function emptyOrRestore(AdminContext $adminContext): Response
    {
        $this->entityManager->getFilters()->disable('softdeleteable');
        $request = $adminContext->getRequest();
        $referer = $request->headers->get('referer');
        if (null === $referer || '' === $referer || '0' === $referer) {
            return $this->redirectToRoute('admin');
        }

        $routeName = $request->query->get('routeName');
        $entity    = $request->attributes->get('entity', null);
        $uuid      = $request->attributes->get('uuid', null);
        match ($routeName) {
            'admin_restore' => $this->adminRestore($entity, $uuid),
            'admin_empty'   => $this->adminEmpty($entity),
            default         => throw new Exception('Route not found'),
        };

        return $this->redirect($referer);
    }

    #[Route('/admin/{_locale}', name: 'admin', defaults: ['_locale' => 'fr'])]
    #[Override]
    public function index(): Response
    {
        return $this->render(
            'admin/dashboard.html.twig',
            []
        );
    }

    #[Route('/admin/{_locale}/profil', name: 'admin_profil')]
    public function profil(): Response
    {
        $generator = $this->container->get(AdminUrlGenerator::class);

        $generator->setAction(Action::EDIT);
        $generator->setController(ProfilCrudController::class);
        $generator->setEntityId($this->getUser()->getId());

        $url = $generator->generateUrl();

        return $this->redirect($url);
    }

    #[Route('/admin/{_locale}/workflow', name: 'admin_workflow')]
    public function workflow(AdminContext $adminContext): Response
    {
        $request = $adminContext->getRequest();
        $referer = $request->headers->get('referer');
        if (null === $referer || '' === $referer || '0' === $referer) {
            return $this->redirectToRoute('admin');
        }

        $entity     = $request->query->get('entity', null);
        $transition = $request->query->get('transition', null);
        $uid        = $request->query->get('uid', null);

        $this->workflowService->change($entity, $transition, $uid);

        return $this->redirect($referer);
    }

    protected function adminEmpty($entity)
    {
        $repository = $this->getRepository($entity);
        $all        = $repository->findDeleted();
        foreach ($all as $row) {
            $repository->remove($row);
        }

        $repository->flush();
    }

    protected function adminRestore($entity, $uuid): void
    {
        $repository = $this->getRepository($entity);
        $data       = $repository->find($uuid);
        if (is_null($data)) {
            throw new Exception('Data not found');
        }

        $methods = get_class_methods($data);
        if (!in_array('isDeleted', $methods)) {
            throw new Exception('Method not found');
        }

        if ($data->isDeleted()) {
            $data->setDeletedAt(null);
            $this->entityManager->persist($data);
            $this->entityManager->flush();
        }
    }

    protected function getRepository(string $entity)
    {
        try {
            $repository = $this->entityManager->getRepository($entity);
        } catch (Exception) {
            throw new Exception('Entity not found');
        }

        return $repository;
    }
}
