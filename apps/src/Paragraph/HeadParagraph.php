<?php

namespace Labstag\Paragraph;

use Labstag\Entity\HeadParagraph as EntityHeadParagraph;
use Labstag\Entity\Page;
use Labstag\Entity\Paragraph;
use Override;
use Symfony\Component\Translation\TranslatableMessage;

class HeadParagraph extends ParagraphAbstract implements ParagraphInterface
{
    /**
     * @param mixed[] $data
     */
    #[Override]
    public function generate(Paragraph $paragraph, array $data, bool $disable): void
    {
        unset($disable);
        $this->setData($paragraph, [
                'paragraph' => $paragraph,
                'data'      => $data,
            ]);
    }

    public function getClass(): string
    {
        return EntityHeadParagraph::class;
    }

    #[Override]
    public function getName(): TranslatableMessage
    {
        return new TranslatableMessage('Head');
    }

    #[Override]
    public function getType(): string
    {
        return 'head';
    }

    #[Override]
    public function supports(?object $object): bool
    {
        if (is_null($object)) {
            return true;
        }

        return Page::class == $object::class;
    }
}
