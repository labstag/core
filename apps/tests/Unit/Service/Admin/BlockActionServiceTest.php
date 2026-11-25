<?php

declare(strict_types=1);

namespace Labstag\Tests\Unit\Service\Admin;

use Labstag\Service\Admin\BlockActionService;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class BlockActionServiceTest extends TestCase
{
    #[Test]
    #[Group('unit')]
    public function classExists(): void
    {
        $this->assertTrue(class_exists(BlockActionService::class));
    }
}
