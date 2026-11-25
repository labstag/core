<?php

declare(strict_types=1);

namespace Labstag\Tests\Unit\Service;

use Labstag\Service\SecurityService;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class SecurityServiceTest extends TestCase
{

    #[Test]
    #[Group('unit')]
    public function getCurrentClientIpNoRequest(): void
    {
        $service = new SecurityService(
            $this->createMock(\Symfony\Bundle\SecurityBundle\Security::class),
            new \Symfony\Component\HttpFoundation\RequestStack(),
            $this->createMock(\Labstag\Service\FileService::class),
            $this->createMock(\Labstag\Repository\BanIpRepository::class),
            $this->createMock(\Labstag\Repository\RedirectionRepository::class),
            $this->createMock(\Labstag\Repository\HttpErrorLogsRepository::class),
        );
        $this->assertSame('0.0.0.0', $service->getCurrentClientIp());
    }
}
