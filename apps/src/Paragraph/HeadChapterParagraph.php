<?php

namespace Labstag\Paragraph;

use Labstag\Entity\Block;
use Labstag\Entity\Chapter;
use Labstag\Entity\Paragraph;
use Labstag\Lib\ParagraphLib;
use Override;

class HeadChapterParagraph extends ParagraphLib
{
    #[Override]
    public function generate(Paragraph $paragraph, array $data, bool $disable)
    {
        unset($disable);
        if (!isset($data['entity']) || !$data['entity'] instanceof Chapter) {
            $this->setShow($paragraph, false);

            return;
        }

        $this->setData(
            $paragraph,
            [
                'chapter'   => $data['entity'],
                'paragraph' => $paragraph,
                'data'      => $data,
            ]
        );
    }

    #[Override]
    public function getFields(Paragraph $paragraph, $pageName): iterable
    {
        unset($paragraph, $pageName);

        return [];
    }

    #[Override]
    public function getName(): string
    {
        return 'Head chapter';
    }

    #[Override

    ]
    public function getType(): string
    {
        return 'head-chapter';
    }

    #[Override]
    public function useIn(): array
    {
        return [
            Block::class,
        ];
    }
}
