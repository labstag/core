<?php

namespace Labstag\Paragraph;

use Labstag\Entity\Page;
use Labstag\Entity\Paragraph;
use Labstag\Entity\SitemapParagraph as EntitySitemapParagraph;
use Override;

class SitemapParagraph extends ParagraphAbstract implements ParagraphInterface
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

    public function getClass(): string
    {
        return EntitySitemapParagraph::class;
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

    #[Override]
    public function supports(?object $object): bool
    {
        if (is_null($object)) {
            return true;
        }

        $entityRepository                = $this->getRepository($this->getClass());
        $paragraph                       = $entityRepository->findOneBy([]);

        if (!$paragraph instanceof Paragraph) {
            return $object instanceof Page;
        }

        $parent = $this->paragraphService->getEntityParent($paragraph);

        return $parent->value->getId() == $object->getId();
    }
}
