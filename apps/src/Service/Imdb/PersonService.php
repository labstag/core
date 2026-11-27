<?php

namespace Labstag\Service\Imdb;

use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Labstag\Api\TheMovieDbApi;
use Labstag\Entity\Person;
use Labstag\Message\PersonMessage;
use Labstag\Service\File\FileUploadService;
use Labstag\Service\MessageDispatcherService;

class PersonService
{
    public function __construct(
        private MessageDispatcherService $messageDispatcherService,
        private EntityManagerInterface $entityManager,
        private FileUploadService $fileUploadService,
        private TheMovieDbApi $theMovieDbApi,
    )
    {
    }

    public function getPerson(array $data): Person
    {
        $entityRepository = $this->entityManager->getRepository(Person::class);
        $person           = $entityRepository->findOneBy(
            [
                'tmdb' => $data['id'],
            ]
        );
        if (!$person instanceof Person) {
            $person = new Person();
            $person->setEnable(true);
            $person->setTmdb($data['id']);
            $person->setTitle($data['name']);
            $entityRepository->save($person);
            $this->messageDispatcherService->dispatch(new PersonMessage($person->getId()));
        }

        return $person;
    }

    public function update(Person $person): bool
    {
        $entityRepository = $this->entityManager->getRepository(Person::class);
        $details          = $this->theMovieDbApi->getDetailPerson($person);
        if (!isset($details['tmdb']) || is_null($details['tmdb'])) {
            $entityRepository->delete($person);

            return false;
        }

        $statuses = [
            $this->updatePerson($person, $details),
            $this->updateImageProfile($person, $details),
        ];

        return in_array(true, $statuses, true);
    }

    /**
     * @param array<string, mixed> $details
     */
    private function updateImageProfile(Person $person, array $details): bool
    {
        $poster = $this->theMovieDbApi->images()->getProfileUrl($details['tmdb']['profile_path'] ?? '');
        if (is_null($poster)) {
            return false;
        }

        $this->fileUploadService->setUploadedFile($poster, $person, 'profileFile');
        $entityRepository = $this->entityManager->getRepository(Person::class);
        $entityRepository->save($person);

        return true;
    }

    private function updatePerson(Person $person, array $data): bool
    {
        if (!isset($data['tmdb'])) {
            return false;
        }

        $person->setGender($data['tmdb']['gender'] ?? null);
        $person->setPlaceOfBirth($data['tmdb']['place_of_birth'] ?: null);
        $person->setBiography($data['tmdb']['biography'] ?? null);
        if (!is_null($data['tmdb']['birthday'])) {
            $birthday = new DateTime($data['tmdb']['birthday']);
            $person->setBirthday($birthday);
        }

        if (!is_null($data['tmdb']['deathday'])) {
            $deathday = new DateTime($data['tmdb']['deathday']);
            $person->setDeathday($deathday);
        }

        return true;
    }
}
