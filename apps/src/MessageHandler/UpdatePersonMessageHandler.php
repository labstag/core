<?php

declare(strict_types=1);

namespace Labstag\MessageHandler;

use Labstag\Message\PersonMessage;
use Labstag\Message\UpdatePersonMessage;
use Labstag\Repository\PersonRepository;
use Labstag\Service\MessageDispatcherService;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final class UpdatePersonMessageHandler
{
    public function __construct(
        private MessageDispatcherService $messageDispatcherService,
        private PersonRepository $personRepository,
    )
    {
    }

    public function __invoke(UpdatePersonMessage $updatePersonMessage): void
    {
        unset($updatePersonMessage);
        $personIds = $this->personRepository->findStale();
        foreach ($personIds as $personId) {
            $this->messageDispatcherService->dispatch(new PersonMessage($personId));
        }
    }
}
