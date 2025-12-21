<?php

namespace Labstag\Service\Imdb;

use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Labstag\Api\TheMovieDbApi;
use Labstag\Entity\Casting;
use Labstag\Entity\Episode;
use Labstag\Entity\Movie;
use Labstag\Entity\Person;
use Labstag\Entity\Season;
use Labstag\Entity\Serie;
use Labstag\Message\PersonMessage;
use Labstag\Service\FileService;
use Labstag\Service\MessageDispatcherService;
use Symfony\Component\Messenger\MessageBusInterface;

final class PersonService
{
    public function __construct(
        private MessageDispatcherService $messageBus,
        private EntityManagerInterface $entityManager,
        private FileService $fileService,
        private TheMovieDbApi $theMovieDbApi,
    )
    {
    }

    public function addToCastingEpisode(Person $person, Episode $episode, array $data): Casting
    {
        $entityRepository = $this->entityManager->getRepository(Casting::class);
        $casting          = $entityRepository->findOneBy(
            [
                'refEpisode' => $episode,
                'refPerson'  => $person,
            ]
        );
        if (!$casting instanceof Casting) {
            $casting = new Casting();
            $casting->setRefEpisode($episode);
            $casting->setRefPerson($person);
        }

        return $this->addToCasting($casting, $data);
    }

    public function addToCastingMovie(Person $person, Movie $movie, array $data): Casting
    {
        $entityRepository = $this->entityManager->getRepository(Casting::class);
        $casting          = $entityRepository->findOneBy(
            [
                'refMovie'  => $movie,
                'refPerson' => $person,
            ]
        );
        if (!$casting instanceof Casting) {
            $casting = new Casting();
            $casting->setRefMovie($movie);
            $casting->setRefPerson($person);
        }

        return $this->addToCasting($casting, $data);
    }

    public function addToCastingSeason(Person $person, Season $season, array $data): Casting
    {
        $entityRepository = $this->entityManager->getRepository(Casting::class);
        $casting          = $entityRepository->findOneBy(
            [
                'refSeason' => $season,
                'refPerson' => $person,
            ]
        );
        if (!$casting instanceof Casting) {
            $casting = new Casting();
            $casting->setRefSeason($season);
            $casting->setRefPerson($person);
        }

        return $this->addToCasting($casting, $data);
    }

    public function addToCastingSerie(Person $person, Serie $serie, array $data): Casting
    {
        $entityRepository = $this->entityManager->getRepository(Casting::class);
        $casting          = $entityRepository->findOneBy(
            [
                'refSerie'  => $serie,
                'refPerson' => $person,
            ]
        );
        if (!$casting instanceof Casting) {
            $casting = new Casting();
            $casting->setRefSerie($serie);
            $casting->setRefPerson($person);
        }

        return $this->addToCasting($casting, $data);
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
            $person->setTmdb($data['id']);
            $person->setTitle($data['name']);
            $entityRepository->save($person);
            $this->messageBus->dispatch(new PersonMessage($person->getId()));
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

    private function addToCasting(Casting $casting, array $data): Casting
    {
        $entityRepository = $this->entityManager->getRepository(Casting::class);
        $casting->setKnownForDepartment($data['known_for_department'] ?? null);
        $casting->setFigure($data['character'] ?? null);

        $entityRepository->save($casting);

        return $casting;
    }

    /**
     * @param array<string, mixed> $details
     */
    private function updateImageProfile(Person $person, array $details): bool
    {
        $poster = $this->theMovieDbApi->images()->getProfileUrl($details['tmdb']['profile_path'] ?? '');
        if (is_null($poster)) {
            $person->setProfileFile();
            $person->setProfile(null);

            return false;
        }

        $this->fileService->setUploadedFile($poster, $person, 'profileFile');

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
