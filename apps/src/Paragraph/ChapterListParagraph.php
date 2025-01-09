<?php

namespace Labstag\Paragraph;

use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use Labstag\Entity\Block;
use Labstag\Entity\Chapter;
use Labstag\Entity\Paragraph;
use Labstag\Entity\Story;
use Labstag\Lib\ParagraphLib;
use Labstag\Repository\ChapterRepository;
use Override;

class ChapterListParagraph extends ParagraphLib
{
    #[Override]
    public function generate(Paragraph $paragraph, array $data, bool $disable): void
    {
        unset($disable);
        if (!isset($data['entity']) || !$data['entity'] instanceof Story) {
            $this->setShow($paragraph, false);

            return;
        }

        /** @var ChapterRepository $serviceEntityRepositoryLib */
        $serviceEntityRepositoryLib = $this->getRepository(Chapter::class);
        $chapters                   = $serviceEntityRepositoryLib->getAllActivateByStory($data['entity']);
        if (0 == count($chapters)) {
            $this->setShow($paragraph, false);

            return;
        }

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
    public function getFields(Paragraph $paragraph, string $pageName): iterable
    {
        unset($paragraph, $pageName);

        yield TextField::new('title');
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
