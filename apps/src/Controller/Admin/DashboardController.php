<?php

namespace Labstag\Controller\Admin;

use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Config\Dashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem;
use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractDashboardController;
use Exception;
use Labstag\Entity\Category;
use Labstag\Entity\Chapter;
use Labstag\Entity\Edito;
use Labstag\Entity\File;
use Labstag\Entity\History;
use Labstag\Entity\Memo;
use Labstag\Entity\Meta;
use Labstag\Entity\Page;
use Labstag\Entity\Post;
use Labstag\Entity\Tag;
use Labstag\Entity\User;
use Override;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class DashboardController extends AbstractDashboardController
{
    public function __construct(
        protected EntityManagerInterface $entityManager
    )
    {
    }

    #[Override]
    public function configureDashboard(): Dashboard
    {
        $dashboard = Dashboard::new();
        $dashboard->setTitle('Www');
        $dashboard->setTranslationDomain('admin');
        $dashboard->renderContentMaximized();
        $dashboard->setLocales(
            [
                'fr',
                'en',
            ]
        );

        return $dashboard;
    }

    #[Override]
    public function configureMenuItems(): iterable
    {
        yield MenuItem::linkToDashboard('Dashboard', 'fa fa-home');
        yield MenuItem::linkToCrud('File', 'fa fa-list', File::class);
        yield MenuItem::subMenu('History')->setSubItems(
            [
                MenuItem::linkToCrud('History', 'fa fa-list', History::class),
                MenuItem::linkToCrud('Category', 'fa fa-list', Category::class)->setController(HistoryCategoryCrudController::class),
                MenuItem::linkToCrud('Tag', 'fa fa-list', Tag::class)->setcontroller(HistoryTagCrudController::class),
            ]
        );
        yield MenuItem::subMenu('Chapter')->setSubItems(
            [
                MenuItem::linkToCrud('Chapter', 'fa fa-list', Chapter::class),
                MenuItem::linkToCrud('Tag', 'fa fa-list', Tag::class)->setcontroller(ChapterTagCrudController::class),
            ]
        );

        yield MenuItem::subMenu('Edito')->setSubItems(
            [
                MenuItem::linkToCrud('Edito', 'fa fa-list', Edito::class),
                MenuItem::linkToCrud('Tag', 'fa fa-list', Tag::class)->setcontroller(EditoTagCrudController::class),
            ]
        );

        yield MenuItem::subMenu('Memo')->setSubItems(
            [
                MenuItem::linkToCrud('Memo', 'fa fa-list', Memo::class),
                MenuItem::linkToCrud('Tag', 'fa fa-list', Tag::class)->setcontroller(MemoTagCrudController::class),
            ]
        );

        yield MenuItem::subMenu('Page')->setSubItems(
            [
                MenuItem::linkToCrud('Page', 'fa fa-list', Page::class),
                MenuItem::linkToCrud('Category', 'fa fa-list', Category::class)->setController(PageCategoryCrudController::class),
                MenuItem::linkToCrud('Tag', 'fa fa-list', Tag::class)->setcontroller(PageTagCrudController::class),
            ]
        );

        yield MenuItem::subMenu('Post')->setSubItems(
            [
                MenuItem::linkToCrud('Post', 'fa fa-list', Post::class),
                MenuItem::linkToCrud('Category', 'fa fa-list', Category::class)->setController(PostCategoryCrudController::class),
                MenuItem::linkToCrud('Tag', 'fa fa-list', Tag::class)->setcontroller(PostTagCrudController::class),
            ]
        );

        yield MenuItem::linkToCrud('Meta', 'fa fa-list', Meta::class);
        yield MenuItem::linkToCrud('User', 'fa fa-user', User::class);
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
