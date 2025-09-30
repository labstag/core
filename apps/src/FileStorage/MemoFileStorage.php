<?php

namespace Labstag\FileStorage;

use Labstag\Entity\Memo;
use Labstag\Interface\FileStorageInterface;
use Labstag\Lib\FileStorageLib;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use League\Flysystem\Local\LocalFilesystemAdapter;
use Symfony\Component\HttpKernel\KernelInterface;

class MemoFileStorage extends FileStorageLib
{
    public function __construct(
        #[Autowire(service: 'flysystem.adapter.memo.storage')]
        LocalFilesystemAdapter $adapter,
        KernelInterface $kernel
    )
    {
        parent::__construct($kernel);
        $this->setAdapter($adapter);
        $this->setType('memo');
    }

    public function getEntity(): ?string
    {
        return Memo::class;
    }
}