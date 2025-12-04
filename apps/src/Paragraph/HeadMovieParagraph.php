<?php

namespace Labstag\Paragraph;

use Labstag\Entity\Block;
use Labstag\Entity\HeadMovieParagraph as EntityHeadMovieParagraph;
use Labstag\Entity\Movie;
use Labstag\Entity\Paragraph;
use Override;
use Symfony\Component\Translation\TranslatableMessage;

class HeadMovieParagraph extends ParagraphAbstract implements ParagraphInterface
{
    /**
     * @param mixed[] $data
     */
    #[Override]
    public function generate(Paragraph $paragraph, array $data, bool $disable): void
    {
        unset($disable);
        if (!isset($data['entity']) || !$data['entity'] instanceof Movie) {
            $this->setShow($paragraph, false);

            return;
        }

        $this->setData($paragraph, [
                'movie'     => $data['entity'],
                'paragraph' => $paragraph,
                'data'      => $data,
            ]);
    }

    public function getClass(): string
    {
        return EntityHeadMovieParagraph::class;
    }

    #[Override]
    public function getName(): TranslatableMessage
    {
        return new TranslatableMessage('Head movie');
    }

    #[Override]
    public function getType(): string
    {
        return 'head-movie';
    }

    #[Override]
    public function supports(?object $object): bool
    {
        if (is_null($object)) {
            return true;
        }

        return $object instanceof Block;
    }
}
