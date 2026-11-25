<?php

declare(strict_types=1);

namespace Labstag\Tests\Unit\Service;

use Labstag\Entity\Configuration;
use Labstag\Repository\ConfigurationRepository;
use Labstag\Service\ConfigurationService;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

final class ConfigurationServiceTest extends TestCase
{

    private ConfigurationRepository&MockObject $repository;

    private ConfigurationService $service;

    protected function setUp(): void
    {
        $this->repository = $this->createMock(ConfigurationRepository::class);
        $this->service = new ConfigurationService($this->repository);
    }

    #[Test]
    #[Group('unit')]
    public function getConfigurationReturnsNullWhenEmpty(): void
    {
        $this->repository->method('findAll')->willReturn([]);
        $this->assertNull($this->service->getConfiguration());
    }

    #[Test]
    #[Group('unit')]
    public function getConfigurationReturnsFirst(): void
    {
        $config = $this->createMock(Configuration::class);
        $this->repository->method('findAll')->willReturn([$config]);
        $this->assertSame($config, $this->service->getConfiguration());
    }

    #[Test]
    #[Group('unit')]
    public function getLocaleTmdbFallback(): void
    {
        $config = $this->createMock(Configuration::class);
        $config->method('getLanguageTmdb')->willReturn(null);
        $this->repository->method('findAll')->willReturn([$config]);
        $this->assertSame('fr-FR', $this->service->getLocaleTmdb());
    }

    #[Test]
    #[Group('unit')]
    public function getTacConfigEmptyWhenNoServices(): void
    {
        $config = $this->createMock(Configuration::class);
        $config->method('getTacServices')->willReturn('');
        $this->repository->method('findAll')->willReturn([$config]);
        $this->assertSame('', $this->service->getTacConfig());
    }

    #[Test]
    #[Group('unit')]
    public function saveDelegates(): void
    {
        $config = $this->createMock(Configuration::class);
        $this->repository->expects($this->once())->method('save')->with($config);
        $this->service->save($config);
    }
}
