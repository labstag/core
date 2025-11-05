<?php

namespace Labstag\FileStorage;

use Labstag\Entity\ImageParagraph;
use Labstag\Entity\PresentationCvParagraph;
use Labstag\Entity\TextImgParagraph;
use Labstag\Entity\TextMediaParagraph;
use Labstag\Entity\VideoParagraph;
use League\Flysystem\Local\LocalFilesystemAdapter;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpKernel\KernelInterface;

class ParagraphFileStorage extends FileStorageAbstract implements FileStorageInterface
{
    public function __construct(
        #[Autowire(service: 'flysystem.adapter.paragraph.storage')]
        LocalFilesystemAdapter $localFilesystemAdapter,
        KernelInterface $kernel,
    )
    {
        parent::__construct($kernel);
        $this->setAdapter($localFilesystemAdapter);
        $this->setType('paragraph');
    }

    #[\Override]
    public function getEntity(): array
    {
        return [
            ImageParagraph::class,
            PresentationCvParagraph::class,
            TextImgParagraph::class,
            TextMediaParagraph::class,
            VideoParagraph::class,
        ];
    }
}
