<?php

declare(strict_types=1);

namespace Labstag\Tests\Unit\Service;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Labstag\Entity\Page;
use Labstag\Enum\PageEnum;
use Labstag\Service\CacheService;
use Labstag\Service\ConfigurationService;
use Labstag\Service\EtagCacheService;
use Labstag\Service\FileService;
use Labstag\Service\FrontService;
use Labstag\Service\MessageDispatcherService;
use Labstag\Service\SitemapService;
use Labstag\Service\SlugService;
use Labstag\Service\ViewResolverService;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

// reuse SiteService mock minimal via createMock
final class FrontServiceTest extends TestCase
{
    #[Test]
    #[Group('unit')]
    public function getPageErrorReturnsPageWhenFound(): void
    {
        $page = new Page();
        $repo = $this->createMock(EntityRepository::class);
        $repo->method('findOneBy')->willReturn($page);

        $em = $this->createMock(EntityManagerInterface::class);
        $em->method('getRepository')->willReturn($repo);

        $service = new FrontService(
            [],
            $this->createMock(CacheService::class),
            $em,
            $this->createMock(MessageDispatcherService::class),
            $this->createMock(FileService::class),
            $this->createMock(ConfigurationService::class),
            $this->createMock(SlugService::class),
            $this->createMock(SitemapService::class),
            $this->createMock(EtagCacheService::class),
            $this->createMock(ViewResolverService::class),
            $this->createMock(\Labstag\Service\SiteService::class),
            $this->createMock(RequestStack::class),
        );

        $this->assertSame($page, $service->getPageError());
    }
}
