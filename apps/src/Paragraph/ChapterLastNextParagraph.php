<?php

namespace Labstag\Paragraph;

use Labstag\Entity\Block;
use Labstag\Entity\Chapter;
use Labstag\Entity\ChapterLastNextParagraph as EntityChapterLastNextParagraph;
use Labstag\Entity\Paragraph;
use Labstag\Repository\ChapterRepository;
use Override;

class ChapterLastNextParagraph extends ParagraphAbstract implements ParagraphInterface
{
    /**
     * @param mixed[] $data
     */
    #[Override]
    public function generate(Paragraph $paragraph, array $data, bool $disable): void
    {
        unset($disable);
        if (!isset($data['entity']) || !$data['entity'] instanceof Chapter) {
            $this->setShow($paragraph, false);

            return;
        }

        $chapter = $data['entity'];
        $story   = $chapter->getRefStory();

        /** @var ChapterRepository $entityRepository */
        $entityRepository = $this->getRepository(Chapter::class);

        $chapters = $entityRepository->getAllActivateByStory($story);

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

    public function getClass(): string
    {
        return EntityChapterLastNextParagraph::class;
    }

    #[Override]
    public function getName(): string
    {
        return 'Chapitre last next';
    }

    #[Override]
    public function getType(): string
    {
        return 'chapter-lastnext';
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
