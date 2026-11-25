<?php

namespace Labstag\Twig\Runtime;

use Doctrine\ORM\EntityManagerInterface;
use Labstag\Entity\Episode;
use Labstag\Entity\Movie;
use Labstag\Entity\Person;
use Labstag\Entity\Serie;
use Twig\Extension\RuntimeExtensionInterface;

class CastingExtensionRuntime implements RuntimeExtensionInterface
{

    protected array $persons = [];

    public function __construct(
        protected EntityManagerInterface $entityManager,
    )
    {
        // Inject dependencies if needed
    }

    public function acting($data): array
    {
        return $this->getByType('Acting', $data);
    }

    public function cast($data): mixed
    {
        if (method_exists($data, 'getCasting')) {
            $tab = $data->getCasting();
            $tab = (is_array($tab) && isset($tab['cast'])) ? $tab['cast'] : [];

            return $this->getPersons($tab);
        }

        return [];
    }

    public function crew($data): mixed
    {
        if (method_exists($data, 'getCasting')) {
            $tab = $data->getCasting();
            $tab = (is_array($tab) && isset($tab['crew'])) ? $tab['crew'] : [];

            return $this->getPersons($tab);
        }

        return [];
    }

    public function episodes($data): array
    {
        $repository  = $this->entityManager->getRepository(Episode::class);
        $episodes    = $repository->getByCasting($data->getId());
        $tab['crew'] = $this->getPersonByEntity($episodes, 'crew', $data->getId());
        $tab['cast'] = $this->getPersonByEntity($episodes, 'cast', $data->getId());

        return $tab;
    }

    public function movies($data): array
    {
        $repository  = $this->entityManager->getRepository(Movie::class);
        $movies      = $repository->getByCasting($data->getId());
        $tab['crew'] = $this->getPersonByEntity($movies, 'crew', $data->getId());
        $tab['cast'] = $this->getPersonByEntity($movies, 'cast', $data->getId());

        return $tab;
    }

    public function series($data): array
    {
        $repository  = $this->entityManager->getRepository(Serie::class);
        $series      = $repository->getByCasting($data->getId());
        $tab['crew'] = $this->getPersonByEntity($series, 'crew', $data->getId());
        $tab['cast'] = $this->getPersonByEntity($series, 'cast', $data->getId());

        return $tab;
    }

    private function getByType(string $type, $data): array
    {
        $casting = [];
        foreach ($data as $row) {
            if ($row->getKnownForDepartment() != $type) {
                continue;
            }

            $casting[] = $row;
        }

        return $casting;
    }

    private function getPerson(string $personId): ?Person
    {
        if (isset($this->persons[$personId])) {
            return $this->persons[$personId];
        }

        $repository               = $this->entityManager->getRepository(Person::class);
        $person                   = $repository->find($personId);
        $this->persons[$personId] = $person;

        return $person;
    }

    private function getPersonByEntity(array $entities, string $type, string $personId): array
    {
        $tab = [];
        foreach ($entities as $entity) {
            $casting = $entity->getCasting();
            if (!isset($casting[$type])) {
                continue;
            }

            foreach ($casting[$type] as $row) {
                if ($row['personId'] != $personId) {
                    continue;
                }

                $row['entity'] = $entity;

                $tab[] = $row;
            }
        }

        return $tab;
    }

    private function getPersons(array $tab): array
    {
        $persons = [];
        foreach ($tab as $row) {
            $row['person'] = $this->getPerson($row['personId'] ?? '');
            $persons[]     = $row;
        }

        return $persons;
    }
}
