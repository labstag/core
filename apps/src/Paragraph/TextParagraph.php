<?php

namespace Labstag\Paragraph;

use Labstag\Entity\Paragraph;
use Labstag\Field\WysiwygField;
use Labstag\Lib\ParagraphLib;
use Override;

class TextParagraph extends ParagraphLib
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
    public function getFields(Paragraph $paragraph, $pageName): iterable
    {
        unset($paragraph, $pageName);
        $wysiwygField = WysiwygField::new('content', 'Texte');

        yield $wysiwygField;
    }

    #[Override]
    public function getName(): string
    {
        return 'Texte';
    }

    #[Override]
    public function getType(): string
    {
        return 'text';
    }

    #[Override]
    public function setData(Paragraph $paragraph, array $data)
    {
        parent::setData(
            $paragraph,
            [
                'paragraph' => $paragraph,
                'data'      => $data,
            ]
        );
    }

    #[Override]
    public function useIn(): array
    {
        return $this->useInAll();
    }
}
