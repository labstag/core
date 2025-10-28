<?php

namespace Labstag\Paragraph;

use EasyCorp\Bundle\EasyAdminBundle\Contracts\Field\FieldInterface;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use Generator;
use Labstag\Entity\Edito;
use Labstag\Entity\Paragraph;
use Labstag\Repository\EditoRepository;
use Override;
use Symfony\Component\Translation\TranslatableMessage;

class EditoParagraph extends ParagraphAbstract
{
    /**
     * @param mixed[] $data
     */
    #[Override]
    public function generate(Paragraph $paragraph, array $data, bool $disable): void
    {
        /** @var EditoRepository $serviceEntityRepositoryAbstract */
        $serviceEntityRepositoryAbstract = $this->getRepository(Edito::class);
        $edito                           = $serviceEntityRepositoryAbstract->findLast();
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

    /**
     * @return Generator<FieldInterface>
     */
    #[Override]
    public function getFields(Paragraph $paragraph, string $pageName): mixed
    {
        unset($paragraph, $pageName);
        yield TextField::new('title', new TranslatableMessage('Title'));
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

    /**
     * @return mixed[]
     */
    #[Override]
    public function useIn(): array
    {
        return $this->useInAll();
    }
}
