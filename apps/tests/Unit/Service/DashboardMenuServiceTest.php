<?php

declare(strict_types=1);

namespace Labstag\Tests\Unit\Service;

use EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem;
use Labstag\Controller\Admin\Factory\MenuItemFactory;
use Labstag\Service\DashboardMenuService;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

final class DashboardMenuServiceTest extends TestCase
{
    #[Test]
    #[Group('unit')]
    public function buildContentMenusReturnsIterable(): void
    {
        $factory = $this->createMock(MenuItemFactory::class);
        $factory->method('createContentSubMenu')->willReturn(MenuItem::subMenu('x'));
        $service = $this->makeService(null, $factory);
        $items   = iterator_to_array($service->buildContentMenus([], []));
        $this->assertNotEmpty($items);
    }

    #[Test]
    #[Group('unit')]
    public function buildSimpleCrudMenusReturnsIterable(): void
    {
        $service = $this->makeService();
        $items   = iterator_to_array($service->buildSimpleCrudMenus());
        $this->assertNotEmpty($items);
    }

    #[Test]
    #[Group('unit')]
    public function buildUtilityMenusReturnsIterable(): void
    {
        $urlGen = $this->createMock(UrlGeneratorInterface::class);
        $urlGen->method('generate')->willReturn('/x');
        $service = $this->makeService($urlGen);
        $items   = iterator_to_array($service->buildUtilityMenus());
        $this->assertNotEmpty($items);
    }

    private function makeService(?UrlGeneratorInterface $urlGen = null, ?MenuItemFactory $factory = null): DashboardMenuService
    {
        return new DashboardMenuService(
            $factory ?? $this->createMock(MenuItemFactory::class),
            $urlGen ?? $this->createMock(UrlGeneratorInterface::class),
        );
    }
}
