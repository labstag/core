<?php

namespace Labstag\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\HttpFoundation\File\File;
use Vich\UploaderBundle\Mapping\Attribute as Vich;

#[ORM\Entity]
#[Vich\Uploadable]
class ImageParagraph extends Paragraph
{

    #[ORM\Column(length: 255, nullable: true)]
    protected ?string $img = null;

    #[Vich\UploadableField(mapping: 'paragraph', fileNameProperty: 'img')]
    protected ?File $imgFile = null;

    public function getImg(): ?string
    {
        return $this->img;
    }

    public function getImgFile(): ?File
    {
        return $this->imgFile;
    }

    public function setImg(?string $img): void
    {
        $this->img = $img;
    }

    public function setImgFile(?File $imgFile = null): void
    {
        $this->imgFile = $imgFile;
    }
}
