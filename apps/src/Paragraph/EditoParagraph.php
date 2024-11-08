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
    public function setData(Paragraph $paragraph, array $data)
    {
        /** @var EditoRepository $repository */
        $repository = $this->getRepository(Edito::class);
        $edito      = $repository->findLast();
        if (!$edito instanceof Edito) {
            return;
        }

        $paragraphsedito = $this->paragraphService->generate($edito->getParagraphs()->getValues(), $data);
        $this->setHeader($paragraph, $this->paragraphService->getContents($paragraphsedito, 'getHeader'));
        $this->setFooter($paragraph, $this->paragraphService->getContents($paragraphsedito, 'getFooter'));

        parent::setData(
            $paragraph,
            [
                'paragraphs' => $paragraphsedito,
                'paragraph'  => $paragraph,
                'edito'      => $edito,
            ]
        );
    }

    #[Override]
    public function useIn(): array
    {
        return $this->useInAll();
    }
}
