<?php

namespace Labstag\Controller\Admin;

use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Dashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem;
use EasyCorp\Bundle\EasyAdminBundle\Config\UserMenu;
use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractDashboardController;
use Exception;
use Labstag\Entity\Block;
use Labstag\Entity\Category;
use Labstag\Entity\Chapter;
use Labstag\Entity\Edito;
use Labstag\Entity\History;
use Labstag\Entity\Memo;
use Labstag\Entity\Meta;
use Labstag\Entity\Page;
use Labstag\Entity\Paragraph;
use Labstag\Entity\Post;
use Labstag\Entity\Tag;
use Labstag\Entity\User;
use Labstag\Form\Admin\OptionType;
use Labstag\Service\SiteService;
use Labstag\Service\UserService;
use Override;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\User\UserInterface;

class DashboardController extends AbstractDashboardController
{
    public function __construct(
        protected EntityManagerInterface $entityManager,
        protected UserService $userService,
        protected SiteService $siteService
    )
    {
    }

    #[Route('/admin/blank', name: 'admin_blank')]
    public function blank(): Response
    {
        return $this->render(
            'admin/blank.html.twig',
            []
        );
    }

    #[Override]
    public function configureDashboard(): Dashboard
    {
        $dashboard = Dashboard::new();
        $dashboard->setTitle('Www');
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
            'history' => HistoryCategoryCrudController::class,
            'page'    => PageCategoryCrudController::class,
            'post'    => PostCategoryCrudController::class,
        ];
        $categories = [];
        foreach ($tab as $key => $value) {
            $categories[$key] = MenuItem::linkToCrud('Category', 'fa fa-list', Category::class);
            $categories[$key]->setController($value);
        }

        $tab = [
            'history' => HistoryTagCrudController::class,
            'chapter' => ChapterTagCrudController::class,
            'edito'   => EditoTagCrudController::class,
            'memo'    => MemoTagCrudController::class,
            'page'    => PageTagCrudController::class,
            'post'    => PostTagCrudController::class,
        ];
        $tags = [];
        foreach ($tab as $key => $value) {
            $tags[$key] = MenuItem::linkToCrud('Tag', 'fa fa-list', Tag::class);
            $tags[$key]->setController($value);
        }

        yield MenuItem::subMenu('History')->setSubItems(
            [
                MenuItem::linkToCrud('List', 'fa fa-list', History::class),
                MenuItem::linkToCrud('new', 'fa fa-list', History::class)->setAction(Action::NEW),
                $categories['history'],
                $tags['history'],
            ]
        );
        yield MenuItem::subMenu('Chapter')->setSubItems(
            [
                MenuItem::linkToCrud('List', 'fa fa-list', Chapter::class),
                MenuItem::linkToCrud('new', 'fa fa-list', Chapter::class)->setAction(Action::NEW),
                $tags['chapter'],
            ]
        );

        yield MenuItem::subMenu('Edito')->setSubItems(
            [
                MenuItem::linkToCrud('List', 'fa fa-list', Edito::class),
                MenuItem::linkToCrud('new', 'fa fa-list', Edito::class)->setAction(Action::NEW),
                $tags['edito'],
            ]
        );

        yield MenuItem::subMenu('Memo')->setSubItems(
            [
                MenuItem::linkToCrud('List', 'fa fa-list', Memo::class),
                MenuItem::linkToCrud('new', 'fa fa-list', Memo::class)->setAction(Action::NEW),
                $tags['memo'],
            ]
        );

        yield MenuItem::subMenu('Page')->setSubItems(
            [
                MenuItem::linkToCrud('List', 'fa fa-list', Page::class),
                MenuItem::linkToCrud('new', 'fa fa-list', Page::class)->setAction(Action::NEW),
                $categories['page'],
                $tags['page'],
            ]
        );

        yield MenuItem::subMenu('Post')->setSubItems(
            [
                MenuItem::linkToCrud('List', 'fa fa-list', Post::class),
                MenuItem::linkToCrud('new', 'fa fa-list', Post::class)->setAction(Action::NEW),
                $categories['post'],
                $tags['post'],
            ]
        );

        yield MenuItem::linkToCrud('Meta', 'fa fa-list', Meta::class);
        yield MenuItem::linkToCrud('Paragraph', 'fa fa-user', Paragraph::class);
        yield MenuItem::linkToCrud('Block', 'fa fa-user', Block::class);
        yield MenuItem::linkToCrud('User', 'fa fa-user', User::class);
        yield MenuItem::linkToRoute('Options', 'fas fa-cog', 'admin_option');
    }

    #[Override]
    public function configureUserMenu(UserInterface $user): UserMenu
    {
        $userMenu = parent::configureUserMenu($user);
        if ($user instanceof User) {
            $userMenu->setGravatarEmail($user->getEmail());
        }

        return $userMenu;
    }

    #[Route('/admin/restore', name: 'admin_restore')]
    #[Route('/admin/empty', name: 'admin_empty')]
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

    #[Route('/admin', name: 'admin')]
    #[Override]
    public function index(): Response
    {
        return $this->render(
            'admin/dashboard.html.twig',
            []
        );
    }

    #[Route('/admin/option', name: 'admin_option')]
    public function option(Request $request): Response
    {
        $data = $this->siteService->getConfiguration();
        $form = $this->createForm(
            OptionType::class,
            $data,
            [
                'attr' => ['id' => 'form_options'],
            ]
        );
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $post = $form->getData();
            $this->addFlash('success', 'Options mis à jour');
            // TODO : Sauvegarde données
            $this->siteService->saveConfiguration($post);
        }

        return $this->render(
            'admin/option.html.twig',
            ['form' => $form]
        );
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
