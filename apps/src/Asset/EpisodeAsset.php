<?php

namespace Labstag\Asset;

use Labstag\Entity\Episode;
use Labstag\Service\ConfigurationService;
use Labstag\Service\FileService;

class EpisodeAsset extends AssetAbstract
{
    public function __construct(
        protected SeasonAsset $seasonAsset,
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

        return $this->seasonAsset->asset($entity->getRefseason(), $field);
    }

    public function placeholder(): string
    {
        $placeholder = $this->globalPlaceholder('episode');
        if ('' !== $placeholder) {
            return $placeholder;
        }

        return $this->seasonAsset->placeholder();
    }

    public function supports(object $entity): bool
    {
        return $entity instanceof Episode;
    }
}
