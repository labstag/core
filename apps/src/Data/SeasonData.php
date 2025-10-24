<?php

namespace Labstag\Data;

use Labstag\Entity\Season;
use Labstag\Repository\SeasonRepository;

class SeasonData extends DataAbstract implements DataInterface
{
    public function __construct(
        private SeasonRepository $seasonRepository,
        private SerieData $serieData,
    )
    {
    }

    public function generateSlug(object $entity): string
    {
        return $this->serieData->generateSlug(
            $entity->getRefserie()
        ) . '/' . $this->getPrefixSeason() . $entity->getNumber();
    }

    public function getEntity(string $slug): object
    {
        return $this->getEntityBySlug($slug);
    }

    public function getPrefixSeason(): string
    {
        return 'saison-';
    }

    public function getTitle(object $entity): string
    {
        return $entity->getTitle();
    }

    public function match(string $slug): bool
    {
        $page = $this->getEntityBySlug($slug);

        return $page instanceof Season;
    }

    public function supports(object $entity): bool
    {
        return $entity instanceof Season;
    }

    private function getEntityBySlug(string $slug): ?object
    {
        if (0 === substr_count($slug, '/')) {
            return null;
        }

        $slugSecond = basename($slug);
        $slugFirst  = dirname($slug);

        if (0 === substr_count($slugSecond, $this->getPrefixSeason())) {
            return null;
        }

        if (false === $this->serieData->match($slugFirst)) {
            return null;
        }

        $serie      = $this->serieData->getEntity($slugFirst);
        $slugSecond = str_replace($this->getPrefixSeason(), '', $slugSecond);

        return $this->seasonRepository->findOneBy(
            [
                'refserie' => $serie,
                'number'   => $slugSecond,
            ]
        );
    }
}
