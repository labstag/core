<?php

namespace Labstag\Data;

use Override;
use Labstag\Entity\Configuration;

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
