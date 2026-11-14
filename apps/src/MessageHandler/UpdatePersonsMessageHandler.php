<?php

declare(strict_types=1);

namespace Labstag\MessageHandler;

use Labstag\Entity\Person;
use Labstag\Message\PersonMessage;
use Labstag\Message\UpdatePersonsMessage;
use Labstag\Repository\PersonRepository;
use Labstag\Service\MessageDispatcherService;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final class UpdatePersonsMessageHandler
{
    public function __construct(
        private MessageDispatcherService $messageDispatcherService,
        private PersonRepository $personRepository,
    ) {
    }

    public function __invoke(UpdatePersonsMessage $updatePersonsMessage): void
    {
        unset($updatePersonsMessage);
        $threshold = new \DateTimeImmutable('-7 days');
        $persons   = $this->personRepository->findToUpdate($threshold);
        foreach ($persons as $person) {
            $this->messageDispatcherService->dispatch(new PersonMessage($person->getId()));
        }
    }
}
