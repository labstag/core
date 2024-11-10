<?php

namespace Labstag\Paragraph;

use Labstag\Entity\History;
use Labstag\Entity\Paragraph;
use Labstag\Lib\ParagraphLib;
use Labstag\Repository\HistoryRepository;
use Override;

class HistoryListParagraph extends ParagraphLib
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
        /** @var HistoryRepository $repository */
        $repository = $this->getRepository(History::class);

        $pagination = $this->getPaginator(
            $repository->getQueryPaginator(),
            $paragraph->getNbr()
        );

        $templates      = $this->templates('header');
        $baseUrlHistory = $this->fileService->getBasePath(History::class, 'imgFile');
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
                'baseUrlHistory' => $baseUrlHistory,
                'pagination'     => $pagination,
                'paragraph'      => $paragraph,
                'data'           => $data,
            ]
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
        return 'History list';
    }

    #[Override]
    public function getType(): string
    {
        return 'history-list';
    }

    #[Override]
    public function useIn(): array
    {
        return $this->useInAll();
    }
}
