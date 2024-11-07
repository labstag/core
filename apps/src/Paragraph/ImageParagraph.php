<?php

namespace Labstag\Paragraph;

use Labstag\Entity\Paragraph;
use Labstag\Lib\ParagraphLib;
use Override;

class ImageParagraph extends ParagraphLib
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
        return 'Image';
    }

    #[Override]
    public function getType(): string
    {
        return 'img';
    }

    #[Override]
    public function useIn(): array
    {
        return $this->useInAll();
    }
}
