<?php

declare(strict_types=1);

namespace Labstag\Tests\Unit\Service\File;

use stdClass;
use Labstag\Service\File\FileStorageService;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Vich\UploaderBundle\Mapping\PropertyMappingFactoryInterface;

final class FileStorageServiceTest extends TestCase
{
    #[Test]
    #[Group('unit')]
    public function getFileInAdapterReturnsNullWhenNoStorages(): void
    {
        $parameterBag = $this->createStub(ParameterBagInterface::class);
        $pmf          = $this->createStub(PropertyMappingFactoryInterface::class);
        $service      = new FileStorageService([], $parameterBag, $pmf);
        $this->assertNull($service->getFileInAdapter('public', 'foo'));
    }

    #[Test]
    #[Group('unit')]
    public function getFilesByAdapterReturnsEmptyWhenNoStorages(): void
    {
        $parameterBag = $this->createStub(ParameterBagInterface::class);
        $pmf          = $this->createStub(PropertyMappingFactoryInterface::class);
        $service      = new FileStorageService([], $parameterBag, $pmf);
        $this->assertSame([], $service->getFilesByAdapter('public'));
    }

    #[Test]
    #[Group('unit')]
    public function getFilesReturnsEmptyWhenNoStorages(): void
    {
        $parameterBag = $this->createStub(ParameterBagInterface::class);
        $pmf          = $this->createStub(PropertyMappingFactoryInterface::class);
        $service      = new FileStorageService([], $parameterBag, $pmf);
        $this->assertSame([], $service->getFiles());
    }

    #[Test]
    #[Group('unit')]
    public function getFullBasePathUsesParameterBag(): void
    {
        $parameterBag = $this->createMock(ParameterBagInterface::class);
        $parameterBag->method('get')->willReturn('/project');
        $pmf     = $this->createStub(PropertyMappingFactoryInterface::class);
        $service = new FileStorageService([], $parameterBag, $pmf);
        $this->assertSame('/project/public', $service->getFullBasePath(new stdClass(), 'img'));
    }
}
