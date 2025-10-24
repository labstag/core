<?php

namespace Labstag\FileStorage;

use Labstag\Entity\Configuration;
use Labstag\FileStorage\Abstract\FileStorageLib;
use League\Flysystem\Local\LocalFilesystemAdapter;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpKernel\KernelInterface;

class ConfigurationFileStorage extends FileStorageLib
{
    public function __construct(
        #[Autowire(service: 'flysystem.adapter.configuration.storage')]
        LocalFilesystemAdapter $localFilesystemAdapter,
        KernelInterface $kernel,
    )
    {
        parent::__construct($kernel);
        $this->setAdapter($localFilesystemAdapter);
        $this->setType('configuration');
    }

    public function getEntity(): ?string
    {
        return Configuration::class;
    }
}
