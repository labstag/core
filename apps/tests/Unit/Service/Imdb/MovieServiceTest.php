<?php

declare(strict_types=1);

namespace Labstag\Tests\Unit\Service\Imdb;

use Labstag\Service\Imdb\MovieService;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class MovieServiceTest extends TestCase
{
    #[Test]
    #[Group('unit')]
    public function classExists(): void
    {
        $this->assertTrue(class_exists(MovieService::class));
    }
}
