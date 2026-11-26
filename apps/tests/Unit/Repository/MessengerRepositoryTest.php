<?php

declare(strict_types=1);

namespace Labstag\Tests\Unit\Repository;

use Labstag\Repository\MessengerRepository;
use PHPUnit\Framework\TestCase;

final class MessengerRepositoryTest extends TestCase
{
    public function testClassExists(): void
    {
        $this->assertTrue(class_exists(MessengerRepository::class));
    }
}
