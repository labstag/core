<?php

namespace Labstag\Paragraph;

use EasyCorp\Bundle\EasyAdminBundle\Contracts\Field\FieldInterface;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use Generator;
use Labstag\Entity\Paragraph;
use Labstag\Entity\Story;
use Labstag\Lib\ParagraphLib;
use Labstag\Repository\StoryRepository;
use Override;

class StoryListParagraph extends ParagraphLib
{
    /**
     * @param mixed[] $data
     */
    #[Override]
    public function generate(Paragraph $paragraph, array $data, bool $disable): void
    {
        unset($disable);
        /** @var StoryRepository $serviceEntityRepositoryLib */
        $serviceEntityRepositoryLib = $this->getRepository(Story::class);

        $pagination = $this->getPaginator($serviceEntityRepositoryLib->getQueryPaginator(), $paragraph->getNbr());

        $templates = $this->templates($paragraph, 'header');
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

        yield TextField::new('title');
        yield $this->addFieldIntegerNbr();
    }

    #[Override]
    public function getName(): string
    {
        return 'Story list';
    }

    #[Override]
    public function getType(): string
    {
        return 'story-list';
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
