<?php

declare(strict_types=1);

namespace Labstag\Tests\Unit\Service\Admin;

use Labstag\Service\Admin\StoryActionService;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class StoryActionServiceTest extends TestCase
{
    #[Test]
    #[Group('unit')]
    public function classExists(): void
    {
        $this->assertTrue(class_exists(StoryActionService::class));
    }
}
