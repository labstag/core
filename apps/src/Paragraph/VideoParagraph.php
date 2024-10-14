<?php

namespace Labstag\Paragraph;

use Labstag\Entity\Paragraph;
use Labstag\Entity\Paragraph\Video;
use Labstag\Lib\ParagraphLib;
use Override;

class VideoParagraph extends ParagraphLib
{
    #[Override]
    public function getEntity()
    {
        return Video::class;
    }

    #[Override]
    public function getFields(Paragraph $paragraph): iterable
    {
        unset($paragraph);

        return [];
    }

    #[Override]
    public function getName(): string
    {
        return 'Video';
    }

    #[Override]
    public function getType(): string
    {
        return 'video';
    }

    #[Override]
    public function useIn(): array
    {
        return $this->useInAll();
    }
}
