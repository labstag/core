<?php

namespace Labstag\Paragraph;

use Labstag\Entity\Paragraph;
use Labstag\Lib\ParagraphLib;
use Override;

class SitemapParagraph extends ParagraphLib
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
        return 'Sitemap';
    }

    #[Override

    ]
    public function getType(): string
    {
        return 'sitemap';
    }

    #[Override]
    public function useIn(): array
    {
        return $this->useInAll();
    }
}
