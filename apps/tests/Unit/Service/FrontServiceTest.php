<?php

declare(strict_types=1);

namespace Labstag\Tests\Unit\Service;

use Labstag\Service\SiteService;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Labstag\Entity\Page;
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
            $this->createStub(CacheService::class),
            $em,
            $this->createStub(MessageDispatcherService::class),
            $this->createStub(FileService::class),
            $this->createStub(ConfigurationService::class),
            $this->createStub(SlugService::class),
            $this->createStub(SitemapService::class),
            $this->createStub(EtagCacheService::class),
            $this->createStub(ViewResolverService::class),
            $this->createStub(SiteService::class),
            new RequestStack(),
        );

        $this->assertSame($page, $service->getPageError());
    }
}
