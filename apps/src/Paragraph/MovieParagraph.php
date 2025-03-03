<?php

namespace Labstag\Paragraph;

use EasyCorp\Bundle\EasyAdminBundle\Contracts\Field\FieldInterface;
use Generator;
use Labstag\Entity\Movie;
use Labstag\Entity\Page;
use Labstag\Entity\Paragraph;
use Labstag\Form\Front\MovieType;
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

        $request = $this->requestStack->getCurrentRequest();
        $query   = $request->query->all();
        if (!isset($query['order'])) {
            $query['order'] = 'createdAt';
        }

        if (!isset($query['orderby'])) {
            $query['orderby'] = 'DESC';
        }

        $pagination = $this->getPaginator($serviceEntityRepositoryLib->getQueryPaginator($query), $paragraph->getNbr());

        $templates = $this->templates($paragraph, 'header');
        $this->setHeader(
            $paragraph,
            $this->render(
                $templates['view'],
                ['pagination' => $pagination]
            )
        );

        $templates = $this->templates($paragraph, 'footer');
        $this->setFooter(
            $paragraph,
            $this->render(
                $templates['view'],
                ['pagination' => $pagination]
            )
        );

        $form = $this->createForm(MovieType::class, $query);

        $this->setData(
            $paragraph,
            [
                'form'       => $form,
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
        return [Page::class];
    }
}
