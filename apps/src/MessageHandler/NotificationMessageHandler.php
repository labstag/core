<?php

namespace Labstag\MessageHandler;

use Labstag\Message\NotificationMessage;
use Labstag\Repository\NotificationRepository;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final class NotificationMessageHandler
{
    public function __construct(
        private NotificationRepository $notificationRepository,
    )
    {
    }

    public function __invoke(NotificationMessage $notificationMessage): void
    {
        unset($notificationMessage);

        $notifications = $this->notificationRepository->getAllBefore1week();
        foreach ($notifications as $notification) {
            $this->notificationRepository->remove($notification);
        }

        $this->notificationRepository->flush();
    }
}
