<?php

namespace Labstag\Paragraph;

use Labstag\Entity\Block;
use Labstag\Entity\History;
use Labstag\Entity\Paragraph;
use Labstag\Lib\ParagraphLib;
use Override;

class HeadHistoryParagraph extends ParagraphLib
{
    #[Override]
    public function content(string $view, Paragraph $paragraph)
    {
        if (!$this->isShow($paragraph)) {
            return null;
        }

        return $this->render(
            $view,
            $this->getData($paragraph)
        );
    }

    #[Override]
    public function generate(Paragraph $paragraph, array $data)
    {
        if (!isset($data['entity']) || !$data['entity'] instanceof History) {
            $this->setShow($paragraph, false);

            return;
        }

        $this->setData(
            $paragraph,
            [
                'history'   => $data['entity'],
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
        return 'Head history';
    }

    #[Override

    ]
    public function getType(): string
    {
        return 'head-history';
    }

    #[Override]
    public function useIn(): array
    {
        return [
            Block::class,
        ];
    }
}
