<?php

declare(strict_types=1);

namespace Labstag\Tests\Unit\Service;

use Labstag\Service\UserService;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class UserServiceTest extends TestCase
{

    #[Test]
    #[Group('unit')]
    public function getLanguages(): void
    {
        $service = new UserService($this->createMock(\Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface::class));
        $this->assertSame(['fr', 'en'], $service->getLanguages());
    }
}
