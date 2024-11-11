<?php

namespace Labstag\Paragraph;

use Labstag\Entity\Chapter;
use Labstag\Entity\Paragraph;
use Labstag\Lib\ParagraphLib;
use Override;

class ChapterLastNextParagraph extends ParagraphLib
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
        if (!isset($data['entity']) || !$data['entity'] instanceof Chapter) {
            $this->setShow($paragraph, false);

            return;
        }

        $chapter = $data['entity'];
        $history = $chapter->getRefHistory();

        $repository = $this->getRepository(Chapter::class);

        $chapters = $repository->getAllEnabledByHistory($history);

        $this->setData(
            $paragraph,
            [
                'position'  => $chapter->getPosition(),
                'chapters'  => $chapters,
                'history'   => $history,
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
        return 'Chapitre last next';
    }

    #[Override

    ]
    public function getType(): string
    {
        return 'chapter-lastnext';
    }

    #[Override]
    public function useIn(): array
    {
        return $this->useInAll();
    }
}
