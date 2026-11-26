<?php

declare(strict_types=1);

namespace Labstag\Tests\Unit\Service;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Labstag\Entity\Configuration;
use Labstag\Entity\User;
use Labstag\Repository\NotificationRepository;
use Labstag\Service\ConfigurationService;
use Labstag\Service\NotificationService;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

final class NotificationServiceTest extends TestCase
{

    private ConfigurationService&MockObject $config;

    private EntityManagerInterface&MockObject $em;

    private NotificationService $service;

    #[Test]
    #[Group('unit')]
    public function setNotificationSaves(): void
    {
        $configuration = $this->createMock(Configuration::class);
        $user          = $this->createStub(User::class);
        $configuration->method('getDefaultUser')->willReturn($user);
        $this->config->method('getConfiguration')->willReturn($configuration);

        $userRepo = $this->createMock(EntityRepository::class);
        $userRepo->method('find')->willReturn(null);
        $notifRepo = $this->createMock(NotificationRepository::class);
        $notifRepo->expects($this->once())->method('save');

        $this->em->method('getRepository')->willReturnCallback(
            function (string $class) use ($notifRepo, $userRepo): MockObject {
                if (User::class === $class) {
                    return $userRepo;
                }

                return $notifRepo;
            }
        );

        $this->service->setNotification('t', 'm');
    }

    protected function setUp(): void
    {
        $this->em      = $this->createMock(EntityManagerInterface::class);
        $this->config  = $this->createMock(ConfigurationService::class);
        $this->service = new NotificationService($this->em, $this->config);
    }
}
