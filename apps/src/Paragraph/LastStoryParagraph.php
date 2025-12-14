<?php

namespace Labstag\Paragraph;

use EasyCorp\Bundle\EasyAdminBundle\Contracts\Field\FieldInterface;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use Generator;
use Labstag\Entity\LastStoryParagraph as EntityLastStoryParagraph;
use Labstag\Entity\Page;
use Labstag\Entity\Paragraph;
use Labstag\Entity\Story;
use Labstag\Enum\PageEnum;
use Labstag\Repository\StoryRepository;
use Override;
use Symfony\Component\Translation\TranslatableMessage;

class LastStoryParagraph extends ParagraphAbstract implements ParagraphInterface
{
    /**
     * @param mixed[] $data
     */
    #[Override]
    public function generate(Paragraph $paragraph, array $data, bool $disable): void
    {
        if (!$paragraph instanceof EntityLastStoryParagraph) {
            $this->setShow($paragraph, false);

            return;
        }

        unset($disable);
        $listing = $this->slugService->getPageByType(PageEnum::STORIES->value);
        /** @var StoryRepository $entityRepository */
        $entityRepository                = $this->getRepository(Story::class);
        $total                           = $entityRepository->findTotalEnable();
        if (!is_object($listing) || !$listing->isEnable() || 0 == $total) {
            $this->setShow($paragraph, false);

            return;
        }

        $nbr     = $paragraph->getNbr();
        $stories = $entityRepository->findLastByNbr($nbr);
        $this->setData(
            $paragraph,
            [
                'listing'   => $listing,
                'total'     => $total,
                'stories'   => $stories,
                'paragraph' => $paragraph,
                'data'      => $data,
            ]
        );
    }

    public function getClass(): string
    {
        return EntityLastStoryParagraph::class;
    }

    /**
     * @return Generator<FieldInterface>
     */
    #[Override]
    public function getFields(Paragraph $paragraph, string $pageName): mixed
    {
        unset($paragraph, $pageName);

        yield TextField::new('title', new TranslatableMessage('Title'));
        yield $this->addFieldIntegerNbr();
    }

    #[Override]
    public function getName(): TranslatableMessage
    {
        return new TranslatableMessage('Last story');
    }

    #[Override]
    public function getType(): string
    {
        return 'last-story';
    }

    #[Override]
    public function supports(?object $object): bool
    {
        if (is_null($object)) {
            return true;
        }

        $entityRepository                = $this->getRepository($this->getClass());
        $paragraph                       = $entityRepository->findOneBy([]);

        if (!$paragraph instanceof Paragraph) {
            return $object instanceof Page && $object->getType() == PageEnum::HOME->value;
        }

        $parent = $this->paragraphService->getEntityParent($paragraph);

        return $parent->value->getId() == $object->getId();
    }
}
