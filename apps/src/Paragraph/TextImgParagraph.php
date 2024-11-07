<?php

namespace Labstag\Paragraph;

use Labstag\Entity\Paragraph;
use Labstag\Lib\ParagraphLib;
use Override;

class TextImgParagraph extends ParagraphLib
{
    #[Override]
    public function content(string $view, Paragraph $paragraph, ?array $data = null)
    {
        return $this->render(
            $view,
            [
                'paragraph' => $paragraph,
                'data'      => $data,
            ]
        );
    }

    #[Override]
    public function getFields(Paragraph $paragraph, $pageName): iterable
    {
        unset($paragraph);
        yield $this->addFieldImageUpload('img', $pageName);
    }

    #[Override]
    public function getName(): string
    {
        return 'Texte image';
    }

    #[Override]
    public function getType(): string
    {
        return 'text-img';
    }

    #[Override]
    public function useIn(): array
    {
        return $this->useInAll();
    }
}
