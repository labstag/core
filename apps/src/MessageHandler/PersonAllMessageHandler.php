<?php

namespace Labstag\MessageHandler;

use Labstag\Message\PersonAllMessage;
use Labstag\Message\PersonMessage;
use Labstag\Repository\PersonRepository;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Messenger\MessageBusInterface;

#[AsMessageHandler]
final class PersonAllMessageHandler
{
    public function __construct(
        private MessageBusInterface $messageBus,
        private PersonRepository $personRepository,
    )
    {
    }

    public function __invoke(PersonAllMessage $personAllMessage): void
    {
        unset($personAllMessage);
        $persons = $this->personRepository->findAll();
        foreach ($persons as $person) {
            $this->messageBus->dispatch(new PersonMessage($person->getId()));
        }
    }
}
