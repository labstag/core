<?php

namespace Labstag\FileStorage;

use Labstag\Entity\Story;
use Labstag\Interface\FileStorageInterface;
use Labstag\Lib\FileStorageLib;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use League\Flysystem\Local\LocalFilesystemAdapter;
use Symfony\Component\HttpKernel\KernelInterface;

class StoryFileStorage extends FileStorageLib
{
    public function __construct(
        #[Autowire(service: 'flysystem.adapter.story.storage')]
        LocalFilesystemAdapter $adapter,
        KernelInterface $kernel
    )
    {
        parent::__construct($kernel);
        $this->setAdapter($adapter);
        $this->setType('story');
    }

    public function getEntity(): ?string
    {
        return Story::class;
    }
}