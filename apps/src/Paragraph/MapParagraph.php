<?php

namespace Labstag\Paragraph;

use Labstag\Entity\Block;
use Labstag\Entity\Edito;
use Labstag\Entity\MapParagraph as EntityMapParagraph;
use Labstag\Entity\Memo;
use Labstag\Entity\Page;
use Labstag\Entity\Paragraph;
use Labstag\Entity\Post;
use Override;
use Symfony\Component\Translation\TranslatableMessage;

class MapParagraph extends ParagraphAbstract implements ParagraphInterface
{
    /**
     * @param mixed[] $data
     */
    #[Override]
    public function generate(Paragraph $paragraph, array $data, bool $disable): void
    {
        unset($disable);
        $this->setData(
            $paragraph,
            [
                'paragraph' => $paragraph,
                'data'      => $data,
            ]
        );
    }

    public function getClass(): string
    {
        return EntityMapParagraph::class;
    }

    #[Override]
    public function getName(): string
    {
        return (string) new TranslatableMessage('Map');
    }

    #[Override]
    public function getType(): string
    {
        return 'map';
    }

    #[Override]
    public function supports(?object $object): bool
    {
        if (is_null($object)) {
            return true;
        }

        $inArray = in_array(
            $object::class,
            [
                Block::class,
                Edito::class,
                Memo::class,
                Page::class,
                Post::class,
            ]
        );

        return $inArray || $object instanceof Block;
    }
}
