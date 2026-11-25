<?php

declare(strict_types=1);

namespace Labstag\Tests\Unit\Service;

use stdClass;
use Labstag\Service\ConfigurationService;
use Labstag\Service\FileService;
use Labstag\Service\SiteService;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\User\UserInterface;

final class SiteServiceTest extends TestCase
{
    #[Test]
    #[Group('unit')]
    public function assetReturnsPlaceholderWhenEntityNull(): void
    {
        $service = $this->makeService();
        $result  = $service->asset(null, 'img', false);
        $this->assertSame('', $result);
    }

    #[Test]
    #[Group('unit')]
    public function getTitleMetaReturnsEmptyWhenNoDatas(): void
    {
        $service = $this->makeService([]);
        $this->assertSame('', $service->getTitleMeta(new stdClass()));
    }

    #[Test]
    #[Group('unit')]
    public function isEnableReturnsTrueWhenUserAuthenticated(): void
    {
        $token = $this->createMock(TokenInterface::class);
        $user  = $this->createStub(UserInterface::class);
        $token->method('getUser')->willReturn($user);

        $tokenStorage = $this->createMock(TokenStorageInterface::class);
        $tokenStorage->method('getToken')->willReturn($token);

        $service = $this->makeService(tokenStorage: $tokenStorage);
        $entity  = new class() {
            public function isEnable(): bool
            {
                return false;
            }
        };
        $this->assertTrue($service->isEnable($entity));
    }

    #[Test]
    #[Group('unit')]
    public function isHomeReturnsFalseWhenNoEntity(): void
    {
        $service = $this->makeService();
        $this->assertFalse($service->isHome([]));
    }

    private function makeService(
        iterable $datas = [],
        ?FileService $fileService = null,
        ?ConfigurationService $config = null,
        ?TokenStorageInterface $tokenStorage = null,
    ): SiteService
    {
        return new SiteService(
            $datas,
            $fileService ?? $this->createMock(FileService::class),
            $config ?? $this->createMock(ConfigurationService::class),
            $tokenStorage ?? $this->createMock(TokenStorageInterface::class),
        );
    }
}
