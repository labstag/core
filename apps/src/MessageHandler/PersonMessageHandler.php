<?php

namespace Labstag\MessageHandler;

use Labstag\Entity\Person;
use Labstag\Message\PersonMessage;
use Labstag\Repository\PersonRepository;
use Labstag\Service\Imdb\PersonService;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final class PersonMessageHandler
{
    public function __construct(
        private PersonService $personService,
        private PersonRepository $personRepository,
    )
    {
    }

    public function __invoke(PersonMessage $personMessage): void
    {
        $personId = $personMessage->getPerson();
        $person   = $this->personRepository->find($personId);
        if (!$person instanceof Person) {
            return;
        }

        $this->personService->update($person);
        $this->personRepository->save($person);
    }
}
