<?php

namespace Labstag\Data;

use Doctrine\ORM\EntityManagerInterface;
use Labstag\Entity\Season;
use Labstag\Service\ConfigurationService;
use Labstag\Service\FileService;

class SeasonData extends DataAbstract implements DataInterface
{
    public function __construct(
        protected SerieData $serieData,
        protected FileService $fileService,
        protected ConfigurationService $configurationService,
        protected EntityManagerInterface $entityManager,
    )
    {
        parent::__construct($fileService, $configurationService, $entityManager);
    }

    #[\Override]
    public function asset(mixed $entity, string $field): string
    {
        $asset = parent::asset($entity, $field);
        if ('' !== $asset) {
            return $asset;
        }

        return $this->serieData->asset($entity->getRefserie(), $field);
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

    public function getTitleMeta(object $entity): string
    {
        return $this->serieData->getTitle($entity->getRefserie()) . ' - ' . $this->getTitle($entity);
    }

    public function match(string $slug): bool
    {
        $page = $this->getEntityBySlug($slug);

        return $page instanceof Season;
    }

    public function placeholder(): string
    {
        $placeholder = $this->globalPlaceholder('season');
        if ('' !== $placeholder) {
            return $placeholder;
        }

        return $this->serieData->configPlaceholder();
    }

    public function supportsAsset(object $entity): bool
    {
        return $entity instanceof Season;
    }

    public function supportsData(object $entity): bool
    {
        return $entity instanceof Season;
    }

    public function supportsShortcode(string $className): bool
    {
        return false;
    }

    protected function getEntityBySlug(string $slug): ?object
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

        return $this->entityManager->getRepository(Season::class)->findOneBy(
            [
                'refserie' => $serie,
                'number'   => $slugSecond,
            ]
        );
    }
}
