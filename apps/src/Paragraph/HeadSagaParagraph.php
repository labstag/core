<?php

namespace Labstag\Paragraph;

use Labstag\Entity\Block;
use Labstag\Entity\HeadSagaParagraph as EntityHeadSagaParagraph;
use Labstag\Entity\Paragraph;
use Labstag\Entity\Saga;
use Override;
use Symfony\Component\Translation\TranslatableMessage;

class HeadSagaParagraph extends ParagraphAbstract implements ParagraphInterface
{
    /**
     * @param mixed[] $data
     */
    #[Override]
    public function generate(Paragraph $paragraph, array $data, bool $disable): void
    {
        unset($disable);
        if (!isset($data['entity']) || !$data['entity'] instanceof Saga) {
            $this->setShow($paragraph, false);

            return;
        }

        $this->setData($paragraph, [
                'saga'      => $data['entity'],
                'paragraph' => $paragraph,
                'data'      => $data,
            ]);
    }

    public function getClass(): string
    {
        return EntityHeadSagaParagraph::class;
    }

    #[Override]
    public function getName(): TranslatableMessage
    {
        return new TranslatableMessage('Head saga');
    }

    #[Override]
    public function getType(): string
    {
        return 'head-saga';
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
