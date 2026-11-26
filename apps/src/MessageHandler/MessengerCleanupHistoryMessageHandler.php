<?php

declare(strict_types=1);

namespace Labstag\MessageHandler;

use Labstag\Message\MessengerCleanupHistoryMessage;
use Labstag\Repository\MessengerRepository;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final class MessengerCleanupHistoryMessageHandler
{
    public function __construct(
        private MessengerRepository $repository,
    )
    {
    }

    public function __invoke(MessengerCleanupHistoryMessage $message): void
    {
        unset($message);
        $this->repository->cleanupExpired();
    }
}
