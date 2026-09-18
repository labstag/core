<?php

namespace Labstag\Service;

use Doctrine\ORM\EntityManagerInterface;
use Labstag\Entity\Notification;
use Labstag\Entity\User;

class NotificationService
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private ConfigurationService $configurationService,
    )
    {
    }

    public function setNotification(string $title, string $message): void
    {
        $notification = new Notification();
        $notification->setRefuser($this->getUser());
        $notification->setTitle($title);
        $notification->setMessage($message);

        $entityRepository = $this->entityManager->getRepository(Notification::class);
        $entityRepository->save($notification);
    }

    private function getUser(): ?object
    {
        $configuration = $this->configurationService->getConfiguration();

        $userId = $configuration->getDefaultUser();

        return $this->entityManager->getRepository(User::class)->find($userId);
    }
}
