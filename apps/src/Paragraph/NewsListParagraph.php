<?php

namespace Labstag\Paragraph;

use Labstag\Entity\Paragraph;
use Labstag\Entity\Post;
use Labstag\Lib\ParagraphLib;
use Labstag\Repository\PostRepository;
use Override;

class NewsListParagraph extends ParagraphLib
{
    #[Override]
    public function content(string $view, Paragraph $paragraph)
    {
        if (!$this->isShow($paragraph)) {
            return null;
        }

        return $this->render(
            $view,
            $this->getData($paragraph)
        );
    }

    #[Override]
    public function generate(Paragraph $paragraph, array $data)
    {
        /** @var PostRepository $repository */
        $repository = $this->getRepository(Post::class);

        $pagination = $this->getPaginator(
            $repository->getQueryPaginator(),
            $paragraph->getNbr()
        );
        $baseUrlPost = $this->fileService->getBasePath(Post::class, 'imgFile');
        $this->setData(
            $paragraph,
            [
                'baseUrlPost' => $baseUrlPost,
                'pagination'  => $pagination,
                'paragraph'   => $paragraph,
                'data'        => $data,
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

    #[Override]
    public function getFields(Paragraph $paragraph, $pageName): iterable
    {
        unset($paragraph, $pageName);

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

    #[Override]
    public function useIn(): array
    {
        return $this->useInAll();
    }
}
