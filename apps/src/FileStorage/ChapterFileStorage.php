<?php

namespace Labstag\FileStorage;

use Labstag\Entity\Chapter;
use Labstag\Interface\FileStorageInterface;
use Labstag\Lib\FileStorageLib;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use League\Flysystem\Local\LocalFilesystemAdapter;
use Symfony\Component\HttpKernel\KernelInterface;

class ChapterFileStorage extends FileStorageLib
{
    public function __construct(
        #[Autowire(service: 'flysystem.adapter.chapter.storage')]
        LocalFilesystemAdapter $adapter,
        KernelInterface $kernel
    )
    {
        parent::__construct($kernel);
        $this->setAdapter($adapter);
        $this->setType('chapter');
    }

    public function getEntity(): ?string
    {
        return Chapter::class;
    }
}