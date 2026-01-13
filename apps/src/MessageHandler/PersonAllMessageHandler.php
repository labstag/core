<?php

namespace Labstag\MessageHandler;

use Labstag\Message\PersonAllMessage;
use Labstag\Message\PersonMessage;
use Labstag\Repository\PersonRepository;
use Labstag\Service\MessageDispatcherService;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final class PersonAllMessageHandler
{
    public function __construct(
        private MessageDispatcherService $messageDispatcherService,
        private PersonRepository $personRepository,
    )
    {
    }

    public function __invoke(PersonAllMessage $personAllMessage): void
    {
        unset($personAllMessage);
        $persons = $this->personRepository->findAll();
        foreach ($persons as $person) {
            $this->messageDispatcherService->dispatch(new PersonMessage($person->getId()));
        }
    }
}
