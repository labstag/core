<?php

namespace Labstag\Paragraph;

use Labstag\Entity\Paragraph;
use Labstag\Lib\ParagraphLib;
use Override;

class EditoParagraph extends ParagraphLib
{
    #[Override]
    public function content(string $view, Paragraph $paragraph, array $data)
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
    public function getFields(Paragraph $paragraph): iterable
    {
        unset($paragraph);

        return [];
    }

    #[Override]
    public function getName(): string
    {
        return 'Edito';
    }

    #[Override]
    public function getType(): string
    {
        return 'edito';
    }

    #[Override]
    public function useIn(): array
    {
        return $this->useInAll();
    }
}
