<?php

namespace Labstag\Paragraph;

use EasyCorp\Bundle\EasyAdminBundle\Contracts\Field\FieldInterface;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use Generator;
use Labstag\Entity\Page;
use Labstag\Entity\Paragraph;
use Labstag\Entity\Story;
use Labstag\Enum\PageEnum;
use Labstag\Repository\StoryRepository;
use Override;
use Symfony\Component\Translation\TranslatableMessage;

class LastStoryParagraph extends ParagraphAbstract
{
    /**
     * @param mixed[] $data
     */
    #[Override]
    public function generate(Paragraph $paragraph, array $data, bool $disable): void
    {
        unset($disable);
        $listing = $this->slugService->getPageByType(PageEnum::STORIES->value);
        /** @var StoryRepository $serviceEntityRepositoryAbstract */
        $serviceEntityRepositoryAbstract = $this->getRepository(Story::class);
        $total                           = $serviceEntityRepositoryAbstract->findTotalEnable();
        if (!is_object($listing) || !$listing->isEnable() || 0 == $total) {
            $this->setShow($paragraph, false);

            return;
        }

        $nbr     = $paragraph->getNbr();
        $stories = $serviceEntityRepositoryAbstract->findLastByNbr($nbr);
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
    public function getName(): string
    {
        return 'Last story';
    }

    #[Override]
    public function getType(): string
    {
        return 'last-story';
    }

    /**
     * @return mixed[]
     */
    #[Override]
    public function useIn(): array
    {
        return [Page::class];
    }
}
