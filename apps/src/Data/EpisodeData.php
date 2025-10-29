<?php

namespace Labstag\Data;

use Doctrine\ORM\EntityManagerInterface;
use Labstag\Entity\Episode;
use Labstag\Service\ConfigurationService;
use Labstag\Service\FileService;

class EpisodeData extends DataAbstract implements DataInterface
{
    public function __construct(
        protected SeasonData $seasonData,
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

        return $this->seasonData->asset($entity->getRefseason(), $field);
    }

    public function generateSlug(object $entity): string
    {
        unset($entity);

        return '';
    }

    public function getEntity(string $slug): object
    {
        unset($slug);

        return new Episode();
    }

    public function getTitle(object $entity): string
    {
        unset($entity);

        return '';
    }

    public function match(string $slug): bool
    {
        unset($slug);

        return false;
    }

    public function placeholder(): string
    {
        $placeholder = $this->globalPlaceholder('episode');
        if ('' !== $placeholder) {
            return $placeholder;
        }

        return $this->seasonData->placeholder();
    }

    public function supportsAsset(object $entity): bool
    {
        return $entity instanceof Episode;
    }

    public function supportsData(object $entity): bool
    {
        unset($entity);

        return false;
    }

    public function supportsShortcode(string $className): bool
    {
        return false;
    }
}
