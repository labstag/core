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
    public function content(string $view, Paragraph $paragraph, ?array $data = null)
    {
        /** @var HistoryRepository $repository  */
        $repository = $this->getRepository(History::class);
        unset($repository);

        return $this->render(
            $view,
            [
                'paragraph' => $paragraph,
                'data'      => $data,
            ]
        );
    }

    #[Override]
    public function getFields(Paragraph $paragraph): iterable
    {
        unset($paragraph);

        return [];
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
