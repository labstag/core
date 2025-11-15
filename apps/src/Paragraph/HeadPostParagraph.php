<?php

namespace Labstag\Paragraph;

use Labstag\Entity\Block;
use Labstag\Entity\HeadPostParagraph as EntityHeadPostParagraph;
use Labstag\Entity\Paragraph;
use Labstag\Entity\Post;
use Override;
use Symfony\Component\Translation\TranslatableMessage;

class HeadPostParagraph extends ParagraphAbstract implements ParagraphInterface
{
    /**
     * @param mixed[] $data
     */
    #[Override]
    public function generate(Paragraph $paragraph, array $data, bool $disable): void
    {
        unset($disable);
        if (!isset($data['entity']) || !$data['entity'] instanceof Post) {
            $this->setShow($paragraph, false);

            return;
        }

        $this->setData(
            $paragraph,
            [
                'post'      => $data['entity'],
                'paragraph' => $paragraph,
                'data'      => $data,
            ]
        );
    }

    public function getClass(): string
    {
        return EntityHeadPostParagraph::class;
    }

    #[Override]
    public function getName(): string
    {
        return (string) new TranslatableMessage('Head post');
    }

    #[Override]
    public function getType(): string
    {
        return 'head-post';
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
