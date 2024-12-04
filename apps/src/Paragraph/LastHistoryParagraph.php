<?php

namespace Labstag\Paragraph;

use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use Labstag\Entity\History;
use Labstag\Entity\Paragraph;
use Labstag\Lib\ParagraphLib;
use Labstag\Repository\HistoryRepository;
use Override;

class LastHistoryParagraph extends ParagraphLib
{
    #[Override]
    public function generate(Paragraph $paragraph, array $data)
    {
        /** @var HistoryRepository $repository */
        $repository = $this->getRepository(History::class);
        $nbr        = $paragraph->getNbr();
        $histories  = $repository->findLastByNbr($nbr);
        $total      = $repository->findTotalEnable();
        $listing    = $this->siteService->getPageByType('history');

        $this->setData(
            $paragraph,
            [
                'listing'   => $listing,
                'total'     => $total,
                'histories' => $histories,
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
        return 'Last history';
    }

    #[Override]
    public function getType(): string
    {
        return 'last-history';
    }

    #[Override]
    public function useIn(): array
    {
        return $this->useInAll();
    }
}
