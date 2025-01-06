<?php

namespace Labstag\Paragraph;

use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use Labstag\Entity\Edito;
use Labstag\Entity\Paragraph;
use Labstag\Lib\ParagraphLib;
use Labstag\Repository\EditoRepository;
use Override;

class EditoParagraph extends ParagraphLib
{
    #[Override]
    public function generate(Paragraph $paragraph, array $data, bool $disable): void
    {
        /** @var EditoRepository $repository */
        $repository = $this->getRepository(Edito::class);
        $edito      = $repository->findLast();
        if (!$edito instanceof Edito) {
            $this->setShow($paragraph, false);

            return;
        }

        $paragraphsedito = $this->paragraphService->generate($edito->getParagraphs()->getValues(), $data, $disable);
        $contents        = $this->paragraphService->getContents($paragraphsedito);
        $this->setHeader($paragraph, $contents->header);
        $this->setFooter($paragraph, $contents->footer);

        $this->setData(
            $paragraph,
            [
                'paragraphs' => $paragraphsedito,
                'paragraph'  => $paragraph,
                'edito'      => $edito,
            ]
        );
    }

    #[Override]
    public function getFields(Paragraph $paragraph, $pageName): iterable
    {
        unset($paragraph, $pageName);
        yield TextField::new('title');
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
