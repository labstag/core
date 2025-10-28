<?php

namespace Labstag\Paragraph;

use Labstag\Entity\Block;
use Labstag\Entity\Paragraph;
use Labstag\Entity\Saga;
use Override;

class HeadSagaParagraph extends ParagraphAbstract
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

        $this->setData(
            $paragraph,
            [
                'saga'      => $data['entity'],
                'paragraph' => $paragraph,
                'data'      => $data,
            ]
        );
    }

    #[Override]
    public function getName(): string
    {
        return 'Head saga';
    }

    #[Override]
    public function getType(): string
    {
        return 'head-saga';
    }

    /**
     * @return mixed[]
     */
    #[Override]
    public function useIn(): array
    {
        return [Block::class];
    }
}
