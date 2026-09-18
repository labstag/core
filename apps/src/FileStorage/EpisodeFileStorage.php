<?php

namespace Labstag\FileStorage;

use Labstag\Entity\Episode;
use League\Flysystem\Local\LocalFilesystemAdapter;
use Override;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpKernel\KernelInterface;

class EpisodeFileStorage extends FileStorageAbstract implements FileStorageInterface
{
    public function __construct(
        #[Autowire(service: 'flysystem.adapter.episode.storage')]
        LocalFilesystemAdapter $localFilesystemAdapter,
        KernelInterface $kernel,
    )
    {
        parent::__construct($kernel);
        $this->setAdapter($localFilesystemAdapter);
        $this->setType('episode');
    }

    #[Override]
    public function getEntity(): array
    {
        return [Episode::class];
    }
}
