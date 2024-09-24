<?php

namespace Labstag\Controller\Admin;

use Override;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Config\Dashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem;
use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractDashboardController;
use Exception;
use Labstag\Entity\Category;
use Labstag\Entity\Chapter;
use Labstag\Entity\Edito;
use Labstag\Entity\History;
use Labstag\Entity\Memo;
use Labstag\Entity\Meta;
use Labstag\Entity\Page;
use Labstag\Entity\Post;
use Labstag\Entity\Tag;
use Labstag\Entity\User;
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
        $dashboard->renderContentMaximized();
        $dashboard->setLocales(
            [
                'fr',
                'en',
            ]
        );

        $debug = $this->getParameter('kernel.debug');
        if ($debug) {
            $dashboard->disableUrlSignatures();
        }

        return $dashboard;
    }

    #[Override]
    public function configureMenuItems(): iterable
    {
        yield MenuItem::linkToDashboard('Dashboard', 'fa fa-home');
        yield MenuItem::linkToCrud('Category', 'fa fa-list', Category::class);
        yield MenuItem::linkToCrud('Chapter', 'fa fa-list', Chapter::class);
        yield MenuItem::linkToCrud('Edito', 'fa fa-list', Edito::class);
        yield MenuItem::linkToCrud('History', 'fa fa-list', History::class);
        yield MenuItem::linkToCrud('Memo', 'fa fa-list', Memo::class);
        yield MenuItem::linkToCrud('Meta', 'fa fa-list', Meta::class);
        yield MenuItem::linkToCrud('Page', 'fa fa-list', Page::class);
        yield MenuItem::linkToCrud('Post', 'fa fa-list', Post::class);
        yield MenuItem::linkToCrud('Tag', 'fa fa-list', Tag::class);
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
        switch ($routeName) {
            case 'admin_restore':
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

                break;
            case 'admin_empty':
                break;
            default:
                throw new Exception('Route not found');
        }

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
