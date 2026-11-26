<?php

namespace Labstag\Tests\Unit\Repository;

use Labstag\Repository\MessengerRepository;
use PHPUnit\Framework\TestCase;

class MessengerRepositoryTest extends TestCase
{
    public function testClassExists(): void
    {
        $this->assertTrue(class_exists(MessengerRepository::class));
    }
}