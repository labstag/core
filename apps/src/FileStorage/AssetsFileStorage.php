<?php

namespace Labstag\FileStorage;

use League\Flysystem\Local\LocalFilesystemAdapter;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpKernel\KernelInterface;

class AssetsFileStorage extends FileStorageAbstract implements FileStorageInterface
{
    public function __construct(
        #[Autowire(service: 'flysystem.adapter.assets.storage')]
        LocalFilesystemAdapter $localFilesystemAdapter,
        KernelInterface $kernel,
    )
    {
        parent::__construct($kernel);
        $this->setAdapter($localFilesystemAdapter);
        $this->setType('assets');
    }
}
