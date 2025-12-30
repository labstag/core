<?php

namespace Labstag\Twig\Runtime;

use Doctrine\ORM\EntityManagerInterface;
use Labstag\Entity\Casting;
use Labstag\Entity\Episode;
use Labstag\Entity\Movie;
use Labstag\Entity\Season;
use Labstag\Entity\Serie;
use Twig\Extension\RuntimeExtensionInterface;

class CastingExtensionRuntime implements RuntimeExtensionInterface
{
    public function __construct(
        protected EntityManagerInterface $entityManager,
    )
    {
        // Inject dependencies if needed
    }

    public function cast($data): mixed
    {
        $repository = $this->entityManager->getRepository(Casting::class);
        $castings = $repository->findWithActiveCastings($data);

        return $castings;
    }

    public function series($data): array
    {
        $tab = [];
        foreach ($data as $row) {
            if ($row->getRefSerie() instanceof Serie) {
                $id = $row->getRefSerie()->getId();
                $tab[$id] = $row;
            }elseif ($row->getRefEpisode() instanceof Episode) {
                $id = $row->getRefEpisode()->getRefSerie()->getId();
                $tab[$id] = $row;
            }elseif ($row->getRefSeason() instanceof Season) {
                $id = $row->getRefSeason()->getRefSerie()->getId();
                $tab[$id] = $row;
            }
        }

        return $tab;
    }

    public function movies($data): array
    {
        $tab = [];
        foreach ($data as $row) {
            if ($row->getRefMovie() instanceof Movie) {
                $id = $row->getRefMovie()->getId();
                $tab[$id] = $row;
            }
        }

        return $tab;
    }

    public function acting($data): array
    {
        return $this->getByType('Acting', $data);
    }

    public function writing($data): array
    {
        return $this->getByType('Writing', $data);
    }

    public function directing($data): array
    {
        return $this->getByType('Directing', $data);
    }

    public function production($data): array
    {
        return $this->getByType('Production', $data);
    }

    public function editing($data): array
    {
        return $this->getByType('Editing', $data);
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
}
