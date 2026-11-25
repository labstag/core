<?php

declare(strict_types=1);

namespace Labstag\Tests\Unit\Service\File;

use Labstag\Service\File\FileImportService;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class FileImportServiceTest extends TestCase
{
    #[Test]
    #[Group('unit')]
    public function classExists(): void
    {
        $this->assertTrue(class_exists(FileImportService::class));
    }
}
