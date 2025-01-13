<?php

namespace Labstag\Paragraph;

use Labstag\Entity\Block;
use Labstag\Entity\Paragraph;
use Labstag\Entity\Post;
use Labstag\Lib\ParagraphLib;

class HeadPostParagraph extends ParagraphLib
{
    #[\Override]
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

    #[\Override]
    public function getFields(Paragraph $paragraph, string $pageName): iterable
    {
        unset($paragraph, $pageName);

        return [];
    }

    #[\Override]
    public function getName(): string
    {
        return 'Head post';
    }

    #[\Override

    ]
    public function getType(): string
    {
        return 'head-post';
    }

    #[\Override]
    public function useIn(): array
    {
        return [Block::class];
    }
}
