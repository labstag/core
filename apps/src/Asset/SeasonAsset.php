<?php

namespace Labstag\Asset;

use Labstag\Entity\Season;
use Labstag\Service\ConfigurationService;
use Labstag\Service\FileService;

class SeasonAsset extends AssetAbstract
{
    public function __construct(
        protected SerieAsset $serieAsset,
        protected FileService $fileService,
        protected ConfigurationService $configurationService,
    )
    {
        parent::__construct($fileService, $configurationService);
    }

    #[\Override]
    public function asset(mixed $entity, string $field): string
    {
        $asset = parent::asset($entity, $field);
        if ('' !== $asset) {
            return $asset;
        }

        return $this->serieAsset->asset($entity->getRefserie(), $field);
    }

    public function placeholder(): string
    {
        $placeholder = $this->globalPlaceholder('season');
        if ('' !== $placeholder) {
            return $placeholder;
        }

        return $this->serieAsset->configPlaceholder();
    }

    public function supports(object $entity): bool
    {
        return $entity instanceof Season;
    }
}
