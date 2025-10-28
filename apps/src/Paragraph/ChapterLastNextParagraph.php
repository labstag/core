<?php

namespace Labstag\Paragraph;

use Labstag\Entity\Block;
use Labstag\Entity\Chapter;
use Labstag\Entity\Paragraph;
use Labstag\Repository\ChapterRepository;
use Override;

class ChapterLastNextParagraph extends ParagraphAbstract
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

        /** @var ChapterRepository $serviceEntityRepositoryAbstract */
        $serviceEntityRepositoryAbstract = $this->getRepository(Chapter::class);

        $chapters = $serviceEntityRepositoryAbstract->getAllActivateByStory($story);

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
    public function getName(): string
    {
        return 'Chapitre last next';
    }

    #[Override]
    public function getType(): string
    {
        return 'chapter-lastnext';
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
