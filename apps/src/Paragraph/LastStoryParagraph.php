<?php

namespace Labstag\Paragraph;

use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use Labstag\Entity\Paragraph;
use Labstag\Entity\Story;
use Labstag\Lib\ParagraphLib;
use Labstag\Repository\StoryRepository;
use Override;

class LastStoryParagraph extends ParagraphLib
{
    #[Override]
    public function generate(Paragraph $paragraph, array $data, bool $disable)
    {
        unset($disable);
        /** @var StoryRepository $repository */
        $repository = $this->getRepository(Story::class);
        $nbr        = $paragraph->getNbr();
        $stories    = $repository->findLastByNbr($nbr);
        $total      = $repository->findTotalEnable();
        $listing    = $this->siteService->getPageByType('story');
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

    #[Override]
    public function getFields(Paragraph $paragraph, $pageName): iterable
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

    #[Override]
    public function useIn(): array
    {
        return $this->useInAll();
    }
}
