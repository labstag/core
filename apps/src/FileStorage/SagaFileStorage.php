<?php

namespace Labstag\FileStorage;

use Labstag\Entity\Saga;
use Labstag\Lib\FileStorageLib;
use League\Flysystem\Local\LocalFilesystemAdapter;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpKernel\KernelInterface;

class SagaFileStorage extends FileStorageLib
{
    public function __construct(
        #[Autowire(service: 'flysystem.adapter.saga.storage')]
        LocalFilesystemAdapter $localFilesystemAdapter,
        KernelInterface $kernel,
    )
    {
        parent::__construct($kernel);
        $this->setAdapter($localFilesystemAdapter);
        $this->setType('saga');
    }

    public function getEntity(): ?string
    {
        return Saga::class;
    }
}
