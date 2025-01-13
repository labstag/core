<?php

namespace Labstag\Paragraph;

use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use Labstag\Entity\Paragraph;
use Labstag\Entity\Story;
use Labstag\Lib\ParagraphLib;
use Labstag\Repository\StoryRepository;

class LastStoryParagraph extends ParagraphLib
{
    #[\Override]
    public function generate(Paragraph $paragraph, array $data, bool $disable): void
    {
        unset($disable);
        /** @var StoryRepository $serviceEntityRepositoryLib */
        $serviceEntityRepositoryLib = $this->getRepository(Story::class);
        $nbr = $paragraph->getNbr();
        $stories = $serviceEntityRepositoryLib->findLastByNbr($nbr);
        $total = $serviceEntityRepositoryLib->findTotalEnable();
        $listing = $this->siteService->getPageByType('story');
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

    #[\Override]
    public function getFields(Paragraph $paragraph, string $pageName): iterable
    {
        unset($paragraph, $pageName);

        yield TextField::new('title');
        yield $this->addFieldIntegerNbr();
    }

    #[\Override]
    public function getName(): string
    {
        return 'Last story';
    }

    #[\Override]
    public function getType(): string
    {
        return 'last-story';
    }

    #[\Override]
    public function useIn(): array
    {
        return $this->useInAll();
    }
}
