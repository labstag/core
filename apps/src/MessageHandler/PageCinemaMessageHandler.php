<?php

namespace Labstag\MessageHandler;

use Labstag\Generate\CinemaGenerate;
use Labstag\Message\PageCinemaMessage;
use Labstag\Service\NotificationService;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final class PageCinemaMessageHandler
{
    public function __construct(
        private CinemaGenerate $cinemaGenerate,
        private NotificationService $notificationService,
    )
    {
    }

    public function __invoke(PageCinemaMessage $pageCinemaMessage): void
    {
        unset($pageCinemaMessage);
        $this->notificationService->setNotification(
            'Cinema Page Generation',
            'The cinema page generation has started.'
        );
        $this->cinemaGenerate->execute();
        $this->notificationService->setNotification(
            'Cinema Page Generation',
            'The cinema page has been created successfully.'
        );
    }
}
