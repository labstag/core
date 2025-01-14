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

class LastNewsParagraph extends ParagraphLib
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
        $nbr = $paragraph->getNbr();
        $news = $serviceEntityRepositoryLib->findLastByNbr($nbr);
        $total = $serviceEntityRepositoryLib->findTotalEnable();
        $listing = $this->siteService->getPageByType('post');
        $this->setData(
            $paragraph,
            [
                'listing'   => $listing,
                'total'     => $total,
                'news'      => $news,
                'paragraph' => $paragraph,
                'data'      => $data,
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
        return 'Last news';
    }

    #[Override]
    public function getType(): string
    {
        return 'last-news';
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
