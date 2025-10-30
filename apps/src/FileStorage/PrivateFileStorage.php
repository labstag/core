<?php

namespace Labstag\FileStorage;

use League\Flysystem\Local\LocalFilesystemAdapter;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpKernel\KernelInterface;

class PrivateFileStorage extends FileStorageAbstract implements FileStorageInterface
{
    public function __construct(
        #[Autowire(service: 'flysystem.adapter.private.storage')]
        LocalFilesystemAdapter $localFilesystemAdapter,
        KernelInterface $kernel,
    )
    {
        parent::__construct($kernel);
        $this->setAdapter($localFilesystemAdapter);
        $this->setType('private');
    }
}
