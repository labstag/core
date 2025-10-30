<?php

namespace Labstag\FileStorage;

use Labstag\Entity\Star;
use League\Flysystem\Local\LocalFilesystemAdapter;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpKernel\KernelInterface;

class StarFileStorage extends FileStorageAbstract implements FileStorageInterface
{
    public function __construct(
        #[Autowire(service: 'flysystem.adapter.star.storage')]
        LocalFilesystemAdapter $localFilesystemAdapter,
        KernelInterface $kernel,
    )
    {
        parent::__construct($kernel);
        $this->setAdapter($localFilesystemAdapter);
        $this->setType('star');
    }

    public function getEntity(): ?string
    {
        return Star::class;
    }
}
