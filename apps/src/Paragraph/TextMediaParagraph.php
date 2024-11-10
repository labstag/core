<?php

namespace Labstag\Paragraph;

use Labstag\Entity\Paragraph;
use Labstag\Lib\ParagraphLib;
use Override;

class TextMediaParagraph extends ParagraphLib
{
    #[Override]
    public function content(string $view, Paragraph $paragraph)
    {
        if (!$this->isShow($paragraph)) {
            return null;
        }

        return $this->render(
            $view,
            $this->getData($paragraph)
        );
    }

    #[Override]
    public function generate(Paragraph $paragraph, array $data)
    {
        $this->setData(
            $paragraph,
            [
                'paragraph' => $paragraph,
                'data'      => $data,
            ]
        );
    }

    #[Override]
    public function getFields(Paragraph $paragraph, $pageName): iterable
    {
        unset($paragraph, $pageName);

        return [];
    }

    #[Override]
    public function getName(): string
    {
        return 'Texte media';
    }

    #[Override

    ]
    public function getType(): string
    {
        return 'text-media';
    }

    #[Override]
    public function useIn(): array
    {
        return $this->useInAll();
    }
}
