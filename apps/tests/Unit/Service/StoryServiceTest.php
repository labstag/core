<?php

declare(strict_types=1);

namespace Labstag\Tests\Unit\Service;

use Labstag\Service\CacheService;
use Labstag\Service\FileService;
use Labstag\Service\StoryService;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Symfony\Contracts\Translation\TranslatorInterface;

final class StoryServiceTest extends TestCase
{
    #[Test]
    #[Group('unit')]
    public function generateFlashBagUsesTranslator(): void
    {
        $translator = $this->createMock(TranslatorInterface::class);
        $translator->method('trans')->willReturn('msg');
        $service = $this->makeService($translator);
        $this->assertSame('msg', $service->generateFlashBag());
    }

    #[Test]
    #[Group('unit')]
    public function getUpdatesReturnsEmptyInitially(): void
    {
        $service = $this->makeService();
        $this->assertSame([], $service->getUpdates());
    }

    private function makeService(?TranslatorInterface $translator = null): StoryService
    {
        return new StoryService(
            $this->createStub(CacheService::class),
            $this->createStub(FileService::class),
            $translator ?? $this->createMock(TranslatorInterface::class),
        );
    }
}
