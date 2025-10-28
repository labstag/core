<?php

namespace Labstag\Paragraph;

use Labstag\Entity\Page;
use Labstag\Entity\Paragraph;
use Override;

class SitemapParagraph extends ParagraphAbstract
{
    /**
     * @param mixed[] $data
     */
    #[Override]
    public function generate(Paragraph $paragraph, array $data, bool $disable): void
    {
        unset($disable);
        $sitemap = $this->sitemapService->getData();
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
    public function getName(): string
    {
        return 'Sitemap';
    }

    #[Override]
    public function getType(): string
    {
        return 'sitemap';
    }

    /**
     * @return mixed[]
     */
    #[Override]
    public function useIn(): array
    {
        return [Page::class];
    }
}
