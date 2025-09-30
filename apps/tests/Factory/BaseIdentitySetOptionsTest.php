<?php

declare(strict_types=1);

namespace App\Tests\Factory;

use Labstag\Lib\Admin\Factory\CrudFieldFactory;
use Labstag\Service\FileService;
use PHPUnit\Framework\TestCase;

final class BaseIdentitySetOptionsTest extends TestCase
{
    private CrudFieldFactory $factory;

    protected function setUp(): void
    {
        $fileService = $this->createMock(FileService::class);
        $fileService->method('getBasePath')->willReturn('/uploads/test');
        $this->factory = new CrudFieldFactory($fileService);
    }

    public function testDefaultIncludesSlugEnableImage(): void
    {
        $set = $this->factory->baseIdentitySet('post', 'index', 'Labstag\\Entity\\Post');
        $props = array_map(fn($f) => $f->getAsDto()->getProperty(), $set);
        self::assertContains('slug', $props);
        self::assertContains('enable', $props);
        self::assertContains('img', $props); // index mode => image displayed as img
    }

    public function testDisableSlug(): void
    {
        $set = $this->factory->baseIdentitySet('memo', 'index', 'Labstag\\Entity\\Memo', withSlug: false);
        $props = array_map(fn($f) => $f->getAsDto()->getProperty(), $set);
        self::assertNotContains('slug', $props);
        self::assertContains('enable', $props);
    }

    public function testDisableEnable(): void
    {
        $set = $this->factory->baseIdentitySet('tag', 'index', 'Labstag\\Entity\\Tag', withEnable: false);
        $props = array_map(fn($f) => $f->getAsDto()->getProperty(), $set);
        self::assertNotContains('enable', $props);
        self::assertContains('slug', $props);
    }

    public function testDisableImage(): void
    {
        $set = $this->factory->baseIdentitySet('category', 'index', 'Labstag\\Entity\\Category', withImage: false);
        $props = array_map(fn($f) => $f->getAsDto()->getProperty(), $set);
        self::assertNotContains('img', $props);
    }

    public function testDisableSlugEnableImage(): void
    {
        $set = $this->factory->baseIdentitySet('template', 'index', 'Labstag\\Entity\\Template', withSlug: false, withImage: false, withEnable: false);
        $props = array_map(fn($f) => $f->getAsDto()->getProperty(), $set);
        self::assertNotContains('slug', $props);
        self::assertNotContains('enable', $props);
        self::assertNotContains('img', $props);
    }
}
