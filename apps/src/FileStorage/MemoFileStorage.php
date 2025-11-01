<?php

namespace Labstag\FileStorage;

use Labstag\Entity\Memo;
use League\Flysystem\Local\LocalFilesystemAdapter;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpKernel\KernelInterface;

class MemoFileStorage extends FileStorageAbstract implements FileStorageInterface
{
    public function __construct(
        #[Autowire(service: 'flysystem.adapter.memo.storage')]
        LocalFilesystemAdapter $localFilesystemAdapter,
        KernelInterface $kernel,
    )
    {
        parent::__construct($kernel);
        $this->setAdapter($localFilesystemAdapter);
        $this->setType('memo');
    }

    public function getEntity(): ?string
    {
        return Memo::class;
    }
}
