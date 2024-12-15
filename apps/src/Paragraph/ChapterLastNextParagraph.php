<?php

namespace Labstag\Paragraph;

use Labstag\Entity\Chapter;
use Labstag\Entity\Paragraph;
use Labstag\Lib\ParagraphLib;
use Override;

class ChapterLastNextParagraph extends ParagraphLib
{
    #[Override]
    public function generate(Paragraph $paragraph, array $data)
    {
        if (!isset($data['entity']) || !$data['entity'] instanceof Chapter) {
            $this->setShow($paragraph, false);

            return;
        }

        $chapter = $data['entity'];
        $story = $chapter->getRefStory();

        $repository = $this->getRepository(Chapter::class);

        $chapters = $repository->getAllActivateByStory($story);

        $this->setData(
            $paragraph,
            [
                'position'  => $chapter->getPosition(),
                'chapters'  => $chapters,
                'story'     => $story,
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
