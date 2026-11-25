<?php

declare(strict_types=1);

namespace Labstag\Tests\Unit\Service;

use stdClass;
use Doctrine\ORM\EntityManagerInterface;
use Labstag\Service\FileService;
use Labstag\Service\MetaService;
use Labstag\Service\SiteService;
use Labstag\Service\ViewResolverService;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Twig\Environment;

final class MetaServiceTest extends TestCase
{
    #[Test]
    #[Group('unit')]
    public function getEntityParentReturnsNullWhenMetaNull(): void
    {
        $service = new MetaService(
            [],
            $this->createStub(Environment::class),
            $this->createStub(EntityManagerInterface::class),
            $this->createStub(ViewResolverService::class),
            $this->createStub(FileService::class),
            $this->createStub(SiteService::class),
        );
        $this->assertNull($service->getEntityParent(null));
    }

    #[Test]
    #[Group('unit')]
    public function getImageForMetatagsReturnsNullWhenAssetEmpty(): void
    {
        $site = $this->createMock(SiteService::class);
        $site->method('asset')->willReturn('');

        $service = new MetaService(
            [],
            $this->createStub(Environment::class),
            $this->createStub(EntityManagerInterface::class),
            $this->createStub(ViewResolverService::class),
            $this->createStub(FileService::class),
            $site,
        );
        $entity = new stdClass();
        $this->assertNull($service->getImageForMetatags($entity));
    }

    #[Test]
    #[Group('unit')]
    public function getJsonLdReturnsEmptyWhenNoDatas(): void
    {
        $service = new MetaService(
            [],
            $this->createStub(Environment::class),
            $this->createStub(EntityManagerInterface::class),
            $this->createStub(ViewResolverService::class),
            $this->createStub(FileService::class),
            $this->createStub(SiteService::class),
        );
        $entity = new stdClass();
        $this->assertSame('', $service->getJsonLd($entity));
    }
}
