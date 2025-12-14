<?php

namespace Labstag\Data;

use Labstag\Entity\Configuration;
use Override;

class ConfigurationData extends DataAbstract implements DataInterface
{
    #[Override]
    public function getEntity(?string $slug): object
    {
        unset($slug);

        return $this->configurationService->getConfiguration();
    }

    #[Override]
    public function placeholder(): string
    {
        return $this->configPlaceholder();
    }

    #[Override]
    public function supportsAsset(object $entity): bool
    {
        return $entity instanceof Configuration;
    }
}
