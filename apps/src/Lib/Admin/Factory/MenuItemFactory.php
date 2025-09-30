<?php

namespace Labstag\Lib\Admin\Factory;

use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Menu\CrudMenuItem;
use EasyCorp\Bundle\EasyAdminBundle\Config\Menu\SubMenuItem;
use EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem;
use Labstag\Controller\Admin\ChapterTagCrudController;
use Labstag\Controller\Admin\MovieCategoryCrudController;
use Labstag\Controller\Admin\MovieTagCrudController;
use Labstag\Controller\Admin\PageCategoryCrudController;
use Labstag\Controller\Admin\PageTagCrudController;
use Labstag\Controller\Admin\PostCategoryCrudController;
use Labstag\Controller\Admin\PostTagCrudController;
use Labstag\Controller\Admin\StoryCategoryCrudController;
use Labstag\Controller\Admin\StoryTagCrudController;
use Symfony\Component\Translation\TranslatableMessage;

/**
 * Factory for generating dashboard menu items to reduce duplication in DashboardController.
 */
final class MenuItemFactory
{
    /**
     * @return array<string, CrudMenuItem>
     */
    public function createCategoryMenuItems(): array
    {
        $categoryControllers = [
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

        return $this->createMenuItems($categoryControllers, 'Category', 'fas fa-hashtag');
    }

    public function createContentSubMenu(
        string $type,
        string $label,
        string $icon,
        string $controllerClass,
        ?array $categories = null,
        ?array $tags = null,
        array $additionalItems = [],
    ): SubMenuItem
    {
        $items = [
            MenuItem::linkToCrud(new TranslatableMessage('List'), 'fa fa-list', $controllerClass::getEntityFqcn()),
            MenuItem::linkToCrud(
                new TranslatableMessage('New'),
                'fas fa-plus',
                $controllerClass::getEntityFqcn()
            )->setAction(Action::NEW),
        ];

        // Add additional items (like Sagas for movies)
        foreach ($additionalItems as $additionalItem) {
            $items[] = $additionalItem;
        }

        // Add category if available
        if ($categories && isset($categories[$type])) {
            $items[] = $categories[$type];
        }

        // Add tag if available
        if ($tags && isset($tags[$type])) {
            $items[] = $tags[$type];
        }

        return MenuItem::subMenu(new TranslatableMessage($label), $icon)->setSubItems($items);
    }

    /**
     * @return array<string, CrudMenuItem>
     */
    public function createTagMenuItems(): array
    {
        $tagControllers = [
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
            'movie'   => [
                'crud'       => MovieTagCrudController::getEntityFqcn(),
                'controller' => MovieTagCrudController::class,
            ],
        ];

        return $this->createMenuItems($tagControllers, 'Tag', 'fas fa-tags');
    }

    /**
     * @param array<string, array<string, string>> $controllers
     *
     * @return array<string, CrudMenuItem>
     */
    private function createMenuItems(array $controllers, string $label, string $icon): array
    {
        $menuItems = [];
        foreach ($controllers as $key => $data) {
            $menuItem = MenuItem::linkToCrud(new TranslatableMessage($label), $icon, $data['crud']);
            $menuItem->setController($data['controller']);
            $menuItems[$key] = $menuItem;
        }

        return $menuItems;
    }
}
