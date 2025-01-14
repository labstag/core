<?php

namespace Labstag\Paragraph;

use EasyCorp\Bundle\EasyAdminBundle\Contracts\Field\FieldInterface;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use Generator;
use Labstag\Entity\Paragraph;
use Labstag\Entity\Post;
use Labstag\Lib\ParagraphLib;
use Labstag\Repository\PostRepository;
use Override;

class NewsListParagraph extends ParagraphLib
{
    /**
     * @param mixed[] $data
     */
    #[Override]
    public function generate(Paragraph $paragraph, array $data, bool $disable): void
    {
        unset($disable);
        /** @var PostRepository $serviceEntityRepositoryLib */
        $serviceEntityRepositoryLib = $this->getRepository(Post::class);

        $pagination = $this->getPaginator($serviceEntityRepositoryLib->getQueryPaginator(), $paragraph->getNbr());
        $this->setData(
            $paragraph,
            [
                'pagination' => $pagination,
                'paragraph'  => $paragraph,
                'data'       => $data,
            ]
        );

        $templates = $this->templates('header');
        $this->setHeader(
            $paragraph,
            $this->render(
                $templates['view'],
                ['pagination' => $pagination]
            )
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
        return 'News list';
    }

    #[Override]
    public function getType(): string
    {
        return 'news-list';
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
