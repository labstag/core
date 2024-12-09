<?php

namespace Labstag\Paragraph;

use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use Labstag\Entity\Paragraph;
use Labstag\Entity\Star;
use Labstag\Lib\ParagraphLib;
use Override;

class StarParagraph extends ParagraphLib
{
    #[Override]
    public function generate(Paragraph $paragraph, array $data)
    {
        /** @var HistoryRepository $repository */
        $repository = $this->getRepository(Star::class);

        $pagination = $this->getPaginator(
            $repository->getQueryPaginator(),
            $paragraph->getNbr()
        );

        $templates = $this->templates('header');
        $this->setHeader(
            $paragraph,
            $this->render(
                $templates['view'],
                ['pagination' => $pagination]
            )
        );

        $this->setData(
            $paragraph,
            [
                'pagination' => $pagination,
                'paragraph'  => $paragraph,
                'data'       => $data,
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
        return 'Star';
    }

    #[Override

    ]
    public function getType(): string
    {
        return 'star';
    }

    #[Override]
    public function useIn(): array
    {
        return $this->useInAll();
    }
}
