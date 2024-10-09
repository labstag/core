<?php

namespace Labstag\Lib;

use Labstag\Entity\Chapter;
use Labstag\Entity\Edito;
use Labstag\Entity\History;
use Labstag\Entity\Memo;
use Labstag\Entity\Page;
use Labstag\Entity\Paragraph;
use Labstag\Entity\Post;

abstract class ParagraphLib
{
    public function getEntity()
    {

    }

    public function getFields(Paragraph $paragraph): iterable
    {
        unset($paragraph);

        return [];
    }

    public function getName(): string
    {
        return '';
    }

    public function getType(): string
    {
        return '';
    }

    public function isEnable(): bool
    {
        return true;
    }

    public function useIn(): array
    {
        return [];
    }

    protected function useInAll(): array
    {
        return [
            Chapter::class,
            Edito::class,
            History::class,
            Memo::class,
            Page::class,
            Post::class,
        ];
    }
}
