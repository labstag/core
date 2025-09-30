<?php

declare(strict_types=1);

namespace App\Tests\Factory;

use Labstag\Lib\Admin\Factory\CrudFieldFactory;
use Labstag\Service\FileService;
use PHPUnit\Framework\TestCase;

final class TacBooleanSetTest extends TestCase
{
    private CrudFieldFactory $factory;

    protected function setUp(): void
    {
        $fileService = $this->createMock(FileService::class);
        $fileService->method('getBasePath')->willReturn('/uploads/test');
        $this->factory = new CrudFieldFactory($fileService);
    }

    public function testTacBooleanSetBuildsAllFields(): void
    {
        $names = [
            'tacEnableAnalytics' => 'Enable analytics',
            'tacEnableAds' => 'Enable ads',
            'tacEnablePixel' => 'Enable pixel',
        ];

        $fields = $this->factory->tacBooleanSet($names);

        self::assertCount(count($names), $fields);

        $properties = array_map(fn($f) => $f->getAsDto()->getProperty(), $fields);
        foreach (array_keys($names) as $expectedProperty) {
            self::assertContains($expectedProperty, $properties);
        }
    }
}
