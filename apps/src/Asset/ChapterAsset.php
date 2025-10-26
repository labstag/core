<?php

namespace Labstag\Asset;

use Labstag\Entity\Chapter;
use Labstag\Service\ConfigurationService;
use Labstag\Service\FileService;

class ChapterAsset extends AssetAbstract
{
    public function __construct(
        protected StoryAsset $storyAsset,
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

        return $this->storyAsset->asset($entity->getStory(), $field);
    }

    public function placeholder(): string
    {
        $placeholder = $this->globalPlaceholder('chapter');
        if ('' !== $placeholder) {
            return $placeholder;
        }

        return $this->storyAsset->placeholder();
    }

    public function supports(object $entity): bool
    {
        return $entity instanceof Chapter;
    }
}
