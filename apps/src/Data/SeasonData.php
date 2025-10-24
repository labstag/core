<?php

namespace Labstag\Data;

use Labstag\Entity\Season;
use Labstag\Repository\PageRepository;
use Labstag\Repository\SeasonRepository;
use Labstag\Data\DataInterface;
use Labstag\Data\Abstract\DataLib;

class SeasonData extends DataLib implements DataInterface
{
    public function __construct(
        private PageRepository $pageRepository,
        private SeasonRepository $seasonRepository,
        private SerieData $serieData
    ){
    }

    public function getTitle(object $entity): string
    {
        return $entity->getTitle();
    }

    public function generateSlug(object $entity): string
    {
        return $this->serieData->generateSlug($entity->getRefserie()).'/'.$this->getPrefixSeason().$entity->getNumber();
    }

    public function supports(object $entity): bool
    {
        return $entity instanceof Season;
    }

    public function match(string $slug): bool
    {
        $page = $this->getEntityBySlug($slug);
        if ($page instanceof Season) {
            return true;
        }

        return false;
    }

    public function getEntity(string $slug): object
    {
        return $this->getEntityBySlug($slug);
    }

    public function getPrefixSeason(): string
    {
        return 'saison-';
    }

    private function getEntityBySlug(string $slug): ?object
    {
        if (0 == substr_count($slug, '/')) {
            return null;
        }

        $slugSecond = basename($slug);
        $slugFirst = dirname($slug);

        if (substr_count($slugSecond, $this->getPrefixSeason()) == 0) {
            return null;
        }

        if ($this->serieData->match($slugFirst) == false) {
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
