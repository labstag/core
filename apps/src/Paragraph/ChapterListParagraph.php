<?php

namespace Labstag\Paragraph;

use Labstag\Entity\Block;
use Labstag\Entity\Chapter;
use Labstag\Entity\History;
use Labstag\Entity\Paragraph;
use Labstag\Lib\ParagraphLib;
use Labstag\Repository\ChapterRepository;
use Override;

class ChapterListParagraph extends ParagraphLib
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

        /** @var ChapterRepository $repository */
        $repository = $this->getRepository(Chapter::class);

        $chapters = $repository->getAllEnabledByHistory($data['entity']);

        $this->setData(
            $paragraph,
            [
                'chapters'  => $chapters,
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
        return 'Chapter list';
    }

    #[Override

    ]
    public function getType(): string
    {
        return 'chapter-list';
    }

    #[Override]
    public function useIn(): array
    {
        return [
            Block::class,
        ];
    }
}
