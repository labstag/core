<?php

namespace Labstag\FileStorage;

use Labstag\Entity\Serie;
use League\Flysystem\Local\LocalFilesystemAdapter;
use Override;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpKernel\KernelInterface;

class SerieFileStorage extends FileStorageAbstract implements FileStorageInterface
{
    public function __construct(
        #[Autowire(service: 'flysystem.adapter.serie.storage')]
        LocalFilesystemAdapter $localFilesystemAdapter,
        KernelInterface $kernel,
    )
    {
        parent::__construct($kernel);
        $this->setAdapter($localFilesystemAdapter);
        $this->setType('serie');
    }

    #[Override]
    public function getEntity(): array
    {
        return [Serie::class];
    }
}
