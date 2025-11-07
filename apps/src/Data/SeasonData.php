<?php

namespace Labstag\Data;

use Doctrine\ORM\EntityManagerInterface;
use Labstag\Entity\Season;
use Labstag\Service\ConfigurationService;
use Labstag\Service\FileService;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Contracts\Translation\TranslatorInterface;

class SeasonData extends DataAbstract implements DataInterface
{
    public function __construct(
        protected SerieData $serieData,
        protected FileService $fileService,
        protected ConfigurationService $configurationService,
        protected EntityManagerInterface $entityManager,
        protected RequestStack $requestStack,
        protected TranslatorInterface $translator,
    )
    {
        parent::__construct($fileService, $configurationService, $entityManager, $requestStack, $translator);
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

    #[\Override]
    public function generateSlug(object $entity): string
    {
        return $this->serieData->generateSlug(
            $entity->getRefserie()
        ) . '/' . $this->getPrefixSeason() . $entity->getNumber();
    }

    #[\Override]
    public function getEntity(?string $slug): object
    {
        return $this->getEntityBySlug($slug);
    }

    public function getPrefixSeason(): string
    {
        return 'saison-';
    }

    #[\Override]
    public function getTitle(object $entity): string
    {
        return $entity->getTitle();
    }

    public function getTitleMeta(object $entity): string
    {
        return $this->serieData->getTitle($entity->getRefserie()) . ' - ' . $this->getTitle($entity);
    }

    #[\Override]
    public function match(?string $slug): bool
    {
        $page = $this->getEntityBySlug($slug);

        return $page instanceof Season;
    }

    #[\Override]
    public function placeholder(): string
    {
        $placeholder = $this->globalPlaceholder('season');
        if ('' !== $placeholder) {
            return $placeholder;
        }

        return $this->serieData->configPlaceholder();
    }

    #[\Override]
    public function supportsAsset(object $entity): bool
    {
        return $entity instanceof Season;
    }

    #[\Override]
    public function supportsData(object $entity): bool
    {
        return $entity instanceof Season;
    }

    protected function getEntityBySlug(?string $slug): ?object
    {
        if (0 === substr_count((string) $slug, '/')) {
            return null;
        }

        $slugSecond = basename((string) $slug);
        $slugFirst  = dirname((string) $slug);

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
