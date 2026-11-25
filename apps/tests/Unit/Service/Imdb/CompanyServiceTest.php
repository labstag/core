<?php

declare(strict_types=1);

namespace Labstag\Tests\Unit\Service\Imdb;

use Labstag\Service\Imdb\CompanyService;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class CompanyServiceTest extends TestCase
{
    #[Test]
    #[Group('unit')]
    public function classExists(): void
    {
        $this->assertTrue(class_exists(CompanyService::class));
    }
}
