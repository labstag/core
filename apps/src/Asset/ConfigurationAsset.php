<?php

namespace Labstag\Asset;

use Labstag\Entity\Configuration;

class ConfigurationAsset extends AssetAbstract
{
    public function placeholder(): string
    {
        return $this->configPlaceholder();
    }

    public function supports(object $entity): bool
    {
        return $entity instanceof Configuration;
    }
}
