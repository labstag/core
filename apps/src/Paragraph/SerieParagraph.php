<?php

namespace Labstag\Paragraph;

use EasyCorp\Bundle\EasyAdminBundle\Contracts\Field\FieldInterface;
use Generator;
use Labstag\Entity\Page;
use Labstag\Entity\Paragraph;
use Labstag\Entity\Serie;
use Labstag\Paragraph\Abstract\ParagraphLib;
use Labstag\Repository\SerieRepository;
use Override;

class SerieParagraph extends ParagraphLib
{
    /**
     * @param mixed[] $data
     */
    #[Override]
    public function generate(Paragraph $paragraph, array $data, bool $disable): void
    {
        unset($disable);
        /** @var SerieRepository $serviceEntityRepositoryLib */
        $serviceEntityRepositoryLib = $this->getRepository(
            Serie::class
        );

        $pagination = $this->getPaginator($serviceEntityRepositoryLib->getQueryPaginator(), $paragraph->getNbr());

        $templates = $this->templates($paragraph, 'header');
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

    /**
     * @return Generator<FieldInterface>
     */
    #[Override]
    public function getFields(Paragraph $paragraph, string $pageName): mixed
    {
        unset($paragraph, $pageName);
        yield $this->addFieldIntegerNbr();
    }

    #[Override]
    public function getName(): string
    {
        return 'Serie';
    }

    #[Override]
    public function getType(): string
    {
        return 'serie';
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
