<?php

namespace Labstag\Paragraph;

use Labstag\Entity\Block;
use Labstag\Entity\Paragraph;
use Labstag\Entity\Story;
use Labstag\Lib\ParagraphLib;
use Override;

class HeadStoryParagraph extends ParagraphLib
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

        $this->setData($paragraph, [
            'story'     => $data['entity'],
            'paragraph' => $paragraph,
            'data'      => $data,
        ]);
    }

    #[Override]
    public function getName(): string
    {
        return 'Head story';
    }

    #[Override]
    public function getType(): string
    {
        return 'head-story';
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
