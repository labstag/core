<?php

namespace Labstag\Paragraph;

use Labstag\Entity\Paragraph;
use Labstag\Lib\ParagraphLib;
use Override;

class FormParagraph extends ParagraphLib
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
        unset($paragraph, $pageName);

        return [];
    }

    #[Override]
    public function getName(): string
    {
        return 'Formulaire';
    }

    #[Override]
    public function getType(): string
    {
        return 'form';
    }

    #[Override]
    public function useIn(): array
    {
        return $this->useInAll();
    }
}
