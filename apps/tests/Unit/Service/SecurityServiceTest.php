<?php

declare(strict_types=1);

namespace Labstag\Tests\Unit\Service;

use Labstag\Repository\BanIpRepository;
use Labstag\Repository\HttpErrorLogsRepository;
use Labstag\Repository\RedirectionRepository;
use Labstag\Service\FileService;
use Labstag\Service\SecurityService;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\RequestStack;

final class SecurityServiceTest extends TestCase
{
    #[Test]
    #[Group('unit')]
    public function getCurrentClientIpNoRequest(): void
    {
        $service = new SecurityService(
            $this->createStub(Security::class),
            new RequestStack(),
            $this->createStub(FileService::class),
            $this->createStub(BanIpRepository::class),
            $this->createStub(RedirectionRepository::class),
            $this->createStub(HttpErrorLogsRepository::class),
        );
        $this->assertSame('0.0.0.0', $service->getCurrentClientIp());
    }
}
