<?php

namespace Labstag\Paragraph;

use Labstag\Entity\Block;
use Labstag\Entity\HeadSerieParagraph as EntityHeadSerieParagraph;
use Labstag\Entity\Paragraph;
use Labstag\Entity\Serie;
use Override;
use Symfony\Component\Translation\TranslatableMessage;

class HeadSerieParagraph extends ParagraphAbstract implements ParagraphInterface
{
    /**
     * @param mixed[] $data
     */
    #[Override]
    public function generate(Paragraph $paragraph, array $data, bool $disable): void
    {
        unset($disable);
        if (!isset($data['entity']) || !$data['entity'] instanceof Serie) {
            $this->setShow($paragraph, false);

            return;
        }

        $this->setData(
            $paragraph,
            [
                'serie'     => $data['entity'],
                'paragraph' => $paragraph,
                'data'      => $data,
            ]
        );
    }

    public function getClass(): string
    {
        return EntityHeadSerieParagraph::class;
    }

    #[Override]
    public function getName(): TranslatableMessage
    {
        return new TranslatableMessage('Head serie');
    }

    #[Override]
    public function getType(): string
    {
        return 'head-serie';
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
