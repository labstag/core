<?php

namespace Labstag\Paragraph;

use EasyCorp\Bundle\EasyAdminBundle\Contracts\Field\FieldInterface;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use Generator;
use Labstag\Entity\Page;
use Labstag\Entity\Paragraph;
use Labstag\Entity\Story;
use Labstag\Lib\ParagraphLib;
use Labstag\Repository\StoryRepository;
use Override;

class LastStoryParagraph extends ParagraphLib
{
    /**
     * @param mixed[] $data
     */
    #[Override]
    public function generate(Paragraph $paragraph, array $data, bool $disable): void
    {
        unset($disable);
        /** @var StoryRepository $serviceEntityRepositoryLib */
        $serviceEntityRepositoryLib = $this->getRepository(Story::class);
        $nbr                        = $paragraph->getNbr();
        $stories                    = $serviceEntityRepositoryLib->findLastByNbr($nbr);
        $total                      = $serviceEntityRepositoryLib->findTotalEnable();
        $listing                    = $this->siteService->getPageByType('story');
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

        yield TextField::new('title');
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
