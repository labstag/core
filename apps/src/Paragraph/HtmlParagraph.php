<?php

namespace Labstag\Paragraph;

use Labstag\Entity\Paragraph\Html;
use Labstag\Lib\ParagraphLib;
use Override;

class HtmlParagraph extends ParagraphLib
{
    #[Override]
    public function getEntity()
    {
        return Html::class;
    }

    #[Override]
    public function getName(): string
    {
        return 'HTML';
    }

    #[Override]
    public function getType(): string
    {
        return 'html';
    }

    #[Override]
    public function useIn(): array
    {
        return $this->useInAll();
    }
}
