<?php

declare(strict_types=1);

namespace Labstag\Tests\Unit\Service\Igdb;

use Labstag\Service\Igdb\GameService;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class GameServiceTest extends TestCase
{
    #[Test]
    #[Group('unit')]
    public function classExists(): void
    {
        $this->assertTrue(class_exists(GameService::class));
    }
}
