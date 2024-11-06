<?php

namespace Labstag\Paragraph;

use Labstag\Entity\Edito;
use Labstag\Entity\Paragraph;
use Labstag\Lib\ParagraphLib;
use Labstag\Repository\EditoRepository;
use Override;

class EditoParagraph extends ParagraphLib
{
    #[Override]
    public function content(string $view, Paragraph $paragraph, ?array $data = null)
    {
        /** @var EditoRepository $repository */
        $repository = $this->getRepository(Edito::class);
        $edito      = $repository->findLast();
        if (!$edito instanceof Edito) {
            return null;
        }

        $paragraphsedito = $this->paragraphService->generate($edito->getParagraphs());

        return $this->render(
            $view,
            [
                'paragraphsedito' => $paragraphsedito,
                'paragraph'       => $paragraph,
                'edito'           => $edito,
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
        return 'Edito';
    }

    #[Override]
    public function getType(): string
    {
        return 'edito';
    }

    #[Override]
    public function useIn(): array
    {
        return $this->useInAll();
    }
}
