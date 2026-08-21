<?php

namespace Labstag\Paragraph;

use Labstag\Entity\Block;
use Labstag\Entity\HeadStoryParagraph as EntityHeadStoryParagraph;
use Labstag\Entity\Paragraph;
use Labstag\Entity\Story;
use Override;
use Symfony\Component\Translation\TranslatableMessage;

class HeadStoryParagraph extends ParagraphAbstract implements ParagraphInterface
{
    /**
     * @param mixed[] $data
     */
    #[Override]
    public function generate(Paragraph $paragraph, array $data, bool $disable): void
    {
        unset($disable);
        if (!isset($data['entity']) || !$data['entity'] instanceof Story) {
            $this->setShow($paragraph, false);

            return;
        }

        $this->setData(
            $paragraph,
            [
                'story'     => $data['entity'],
                'paragraph' => $paragraph,
                'data'      => $data,
            ]
        );
    }

    public function getClass(): string
    {
        return EntityHeadStoryParagraph::class;
    }

    #[Override]
    public function getName(): TranslatableMessage
    {
        return new TranslatableMessage('Head story');
    }

    #[Override]
    public function getType(): string
    {
        return 'head-story';
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
