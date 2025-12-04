<?php

namespace Labstag\Paragraph;

use EasyCorp\Bundle\EasyAdminBundle\Contracts\Field\FieldInterface;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use Generator;
use Labstag\Entity\Block;
use Labstag\Entity\Chapter;
use Labstag\Entity\ChapterListParagraph as EntityChapterListParagraph;
use Labstag\Entity\Paragraph;
use Labstag\Entity\Story;
use Labstag\Repository\ChapterRepository;
use Override;
use Symfony\Component\Translation\TranslatableMessage;

class ChapterListParagraph extends ParagraphAbstract implements ParagraphInterface
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

        /** @var ChapterRepository $entityRepository */
        $entityRepository                = $this->getRepository(Chapter::class);
        $chapters                        = $entityRepository->getAllActivateByStory($data['entity']);
        if (0 === count($chapters)) {
            $this->setShow($paragraph, false);

            return;
        }

        $this->setData($paragraph, [
                'chapters'  => $chapters,
                'paragraph' => $paragraph,
                'data'      => $data,
            ]);
    }

    public function getClass(): string
    {
        return EntityChapterListParagraph::class;
    }

    /**
     * @return Generator<FieldInterface>
     */
    #[Override]
    public function getFields(Paragraph $paragraph, string $pageName): mixed
    {
        unset($paragraph, $pageName);

        yield TextField::new('title', new TranslatableMessage('Title'));
    }

    #[Override]
    public function getName(): TranslatableMessage
    {
        return new TranslatableMessage('Chapter list');
    }

    #[Override]
    public function getType(): string
    {
        return 'chapter-list';
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
