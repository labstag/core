<?php

namespace Labstag\Paragraph;

use EasyCorp\Bundle\EasyAdminBundle\Contracts\Field\FieldInterface;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use Generator;
use Labstag\Entity\Movie;
use Labstag\Entity\Paragraph;
use Labstag\Lib\ParagraphLib;
use Labstag\Repository\MovieRepository;
use Override;

class MovieParagraph extends ParagraphLib
{
    /**
     * @param mixed[] $data
     */
    #[Override]
    public function generate(Paragraph $paragraph, array $data, bool $disable): void
    {
        unset($disable);
        /** @var MovieRepository $serviceEntityRepositoryLib */
        $serviceEntityRepositoryLib = $this->getRepository(Movie::class);

        $pagination = $this->getPaginator($serviceEntityRepositoryLib->getQueryPaginator(), $paragraph->getNbr());

        $templates = $this->templates('header');
        $this->setHeader($paragraph, $this->render($templates['view'], [
            'pagination' => $pagination,
        ]));

        $templates = $this->templates('footer');
        $this->setFooter($paragraph, $this->render($templates['view'], [
            'pagination' => $pagination,
        ]));

        $this->setData($paragraph, [
            'pagination' => $pagination,
            'paragraph'  => $paragraph,
            'data'       => $data,
        ]);
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
        return 'Movie';
    }

    #[Override]
    public function getType(): string
    {
        return 'movie';
    }

    /**
     * @return mixed[]
     */
    #[Override]
    public function useIn(): array
    {
        return $this->useInAll();
    }
}
