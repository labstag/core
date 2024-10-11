<?php

namespace Labstag\Paragraph;

use Labstag\Entity\Paragraph;
use Labstag\Entity\Paragraph\Image;
use Labstag\Lib\ParagraphLib;
use Override;

class ImageParagraph extends ParagraphLib
{
    #[Override]
    public function getEntity()
    {
        return Image::class;
    }

    #[Override]
    public function getName(): string
    {
        return 'Image';
    }

    #[Override]
    public function getType(): string
    {
        return 'img';
    }

    public function getFields(Paragraph $paragraph): iterable
    {
        unset($paragraph);

        return [];
    }

    #[Override]
    public function useIn(): array
    {
        return $this->useInAll();
    }
}
