<?php

namespace Labstag\Data;

use Labstag\Entity\Episode;
use Override;

class EpisodeData extends SeasonData implements DataInterface
{
    #[Override]
    public function asset(mixed $entity, string $field): string
    {
        $asset = $this->fileService->asset($entity, $field);
        if ('' !== $asset) {
            return $asset;
        }

        return parent::asset($entity->getRefseason(), 'backdrop');
    }

    public function getDefaultImage(object $entity): ?string
    {
        return $entity->getImg();
    }

    #[Override]
    public function placeholder(): string
    {
        $placeholder = $this->globalPlaceholder('episode');
        if ('' !== $placeholder) {
            return $placeholder;
        }

        return parent::placeholder();
    }

    #[Override]
    public function supportsAsset(object $entity): bool
    {
        return $entity instanceof Episode;
    }
}
