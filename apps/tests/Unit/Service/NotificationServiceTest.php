<?php

declare(strict_types=1);

namespace Labstag\Tests\Unit\Service;

use Labstag\Entity\Configuration;
use Labstag\Service\ConfigurationService;
use Labstag\Service\NotificationService;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

final class NotificationServiceTest extends TestCase
{

    private ConfigurationService&MockObject $config;

    private \Doctrine\ORM\EntityManagerInterface&MockObject $em;

    private NotificationService $service;

    protected function setUp(): void
    {
        $this->em = $this->createMock(\Doctrine\ORM\EntityManagerInterface::class);
        $this->config = $this->createMock(ConfigurationService::class);
        $this->service = new NotificationService($this->em, $this->config);
    }

    #[Test]
    #[Group('unit')]
    public function setNotificationSaves(): void
    {
        $configuration = $this->createMock(Configuration::class);
        $user = $this->createMock(\Labstag\Entity\User::class);
        $configuration->method('getDefaultUser')->willReturn($user);
        $this->config->method('getConfiguration')->willReturn($configuration);

        $userRepo = $this->createMock(\Doctrine\ORM\EntityRepository::class);
        $userRepo->method('find')->willReturn(null);
        $notifRepo = $this->createMock(\Labstag\Repository\NotificationRepository::class);
        $notifRepo->expects($this->once())->method('save');

        $this->em->method('getRepository')->willReturnCallback(function ($class) use ($notifRepo, $userRepo) {
            if ($class === \Labstag\Entity\User::class) {
                return $userRepo;
            }
            return $notifRepo;
        });

        $this->service->setNotification('t', 'm');
    }
}
