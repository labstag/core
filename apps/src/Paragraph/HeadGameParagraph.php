<?php

namespace Labstag\Paragraph;

use Labstag\Entity\Block;
use Labstag\Entity\Game;
use Labstag\Entity\HeadGameParagraph as EntityHeadGameParagraph;
use Labstag\Entity\Paragraph;
use Override;
use Symfony\Component\Translation\TranslatableMessage;

class HeadGameParagraph extends ParagraphAbstract implements ParagraphInterface
{
    /**
     * @param mixed[] $data
     */
    #[Override]
    public function generate(Paragraph $paragraph, array $data, bool $disable): void
    {
        unset($disable);
        if (!isset($data['entity']) || !$data['entity'] instanceof Game) {
            $this->setShow($paragraph, false);

            return;
        }

        $this->setData($paragraph, [
                'game'      => $data['entity'],
                'paragraph' => $paragraph,
                'data'      => $data,
            ]);
    }

    public function getClass(): string
    {
        return EntityHeadGameParagraph::class;
    }

    #[Override]
    public function getName(): TranslatableMessage
    {
        return new TranslatableMessage('Head game');
    }

    #[Override]
    public function getType(): string
    {
        return 'head-game';
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
