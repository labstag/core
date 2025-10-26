<?php

namespace Labstag\Paragraph;

use EasyCorp\Bundle\EasyAdminBundle\Contracts\Field\FieldInterface;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use Generator;
use Labstag\Entity\Page;
use Labstag\Entity\Paragraph;
use Labstag\Entity\Post;
use Labstag\Enum\PageEnum;
use Labstag\Repository\PostRepository;
use Override;
use Symfony\Component\Translation\TranslatableMessage;

class LastNewsParagraph extends ParagraphAbstract
{
    /**
     * @param mixed[] $data
     */
    #[Override]
    public function generate(Paragraph $paragraph, array $data, bool $disable): void
    {
        unset($disable);
        $listing = $this->slugService->getPageByType(PageEnum::POSTS->value);
        /** @var PostRepository $ServiceEntityRepositoryAbstract */
        $ServiceEntityRepositoryAbstract = $this->getRepository(Post::class);
        $total                           = $ServiceEntityRepositoryAbstract->findTotalEnable();
        if (!is_object($listing) || !$listing->isEnable() || 0 == $total) {
            $this->setShow($paragraph, false);

            return;
        }

        $nbr   = $paragraph->getNbr();
        $news  = $ServiceEntityRepositoryAbstract->findLastByNbr($nbr);
        $total = $ServiceEntityRepositoryAbstract->findTotalEnable();
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

        yield TextField::new('title', new TranslatableMessage('Title'));
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
        return [Page::class];
    }
}
