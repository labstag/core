<?php

declare(strict_types=1);

namespace Labstag\Tests\Unit\Service\File;

use Labstag\Service\File\FileCleanupService;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class FileCleanupServiceTest extends TestCase
{
    #[Test]
    #[Group('unit')]
    public function classExists(): void
    {
        $this->assertTrue(class_exists(FileCleanupService::class));
    }
}
