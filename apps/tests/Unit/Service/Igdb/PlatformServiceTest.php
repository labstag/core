<?php

declare(strict_types=1);

namespace Labstag\Tests\Unit\Service\Igdb;

use Labstag\Service\Igdb\PlatformService;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class PlatformServiceTest extends TestCase
{
    #[Test]
    #[Group('unit')]
    public function classExists(): void
    {
        $this->assertTrue(class_exists(PlatformService::class));
    }
}
