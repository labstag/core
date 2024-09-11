<?php

namespace Labstag\Controller\Admin;

use EasyCorp\Bundle\EasyAdminBundle\Config\Dashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractDashboardController;
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
    #[Route('/admin', name: 'admin')]
    public function index(): Response
    {
        return $this->render(
            'admin/dashboard.html.twig',
            []
        );
    }

    public function configureDashboard(): Dashboard
    {
        $dashboard = Dashboard::new();
        $dashboard->setTitle('Www');

        return $dashboard;
    }

    public function configureMenuItems(): iterable
    {
        yield MenuItem::linkToDashboard('Dashboard', 'fa fa-home');
        yield Menuitem::linkToCrud('Category', 'fa fa-list', Category::class);
        yield Menuitem::linkToCrud('Chapter', 'fa fa-list', Chapter::class);
        yield Menuitem::linkToCrud('Edito', 'fa fa-list', Edito::class);
        yield Menuitem::linkToCrud('History', 'fa fa-list', History::class);
        yield Menuitem::linkToCrud('Memo', 'fa fa-list', Memo::class);
        yield Menuitem::linkToCrud('Meta', 'fa fa-list', Meta::class);
        yield Menuitem::linkToCrud('Page', 'fa fa-list', Page::class);
        yield Menuitem::linkToCrud('Post', 'fa fa-list', Post::class);
        yield Menuitem::linkToCrud('Tag', 'fa fa-list', Tag::class);
        yield Menuitem::linkToCrud('User', 'fa fa-user', User::class);
    }
}
