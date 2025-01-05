<?php

namespace Labstag\Paragraph;

use Labstag\Entity\Paragraph;
use Labstag\Lib\ParagraphLib;
use Override;

class SitemapParagraph extends ParagraphLib
{
    #[Override]
    public function generate(Paragraph $paragraph, array $data)
    {
        $sitemap = $this->sitemapService->getData();
        dump($sitemap);
        $this->setData(
            $paragraph,
            [
                'sitemap'   => $sitemap,
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
