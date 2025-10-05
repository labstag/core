<?php

namespace Labstag\Service;

use Labstag\Entity\Configuration;
use Labstag\Repository\ConfigurationRepository;

final class ConfigurationService
{

    private ?Configuration $configuration = null;

    public function __construct(
        private ConfigurationRepository $configurationRepository,
    )
    {
    }

    public function getConfiguration(): ?Configuration
    {
        if ($this->configuration instanceof Configuration) {
            return $this->configuration;
        }

        $configurations = $this->configurationRepository->findAll();

        $this->configuration = $configurations[0] ?? null;

        return $this->configuration;
    }
}
