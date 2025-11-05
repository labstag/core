<?php

namespace Labstag\Paragraph;

use EasyCorp\Bundle\EasyAdminBundle\Contracts\Field\FieldInterface;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use Generator;
use Labstag\Entity\LastNewsParagraph as EntityLastNewsParagraph;
use Labstag\Entity\Page;
use Labstag\Entity\Paragraph;
use Labstag\Entity\Post;
use Labstag\Enum\PageEnum;
use Labstag\Repository\PostRepository;
use Override;
use Symfony\Component\Translation\TranslatableMessage;

class LastNewsParagraph extends ParagraphAbstract implements ParagraphInterface
{
    /**
     * @param mixed[] $data
     */
    #[Override]
    public function generate(Paragraph $paragraph, array $data, bool $disable): void
    {
        if (!$paragraph instanceof EntityLastNewsParagraph) {
            $this->setShow($paragraph, false);

            return;
        }

        unset($disable);
        $listing = $this->slugService->getPageByType(PageEnum::POSTS->value);
        /** @var PostRepository $entityRepository */
        $entityRepository                = $this->getRepository(Post::class);
        $total                           = $entityRepository->findTotalEnable();
        if (!is_object($listing) || !$listing->isEnable() || 0 == $total) {
            $this->setShow($paragraph, false);

            return;
        }

        $nbr   = $paragraph->getNbr();
        $news  = $entityRepository->findLastByNbr($nbr);
        $total = $entityRepository->findTotalEnable();
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

    public function getClass(): string
    {
        return EntityLastNewsParagraph::class;
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
        return (string) new TranslatableMessage('Last news');
    }

    #[Override]
    public function getType(): string
    {
        return 'last-news';
    }

    #[Override]
    public function supports(?object $object): bool
    {
        if (is_null($object)) {
            return true;
        }

        $entityRepository                = $this->getRepository($this->getClass());
        $paragraph                       = $entityRepository->findOneBy([]);

        if (!$paragraph instanceof Paragraph) {
            return $object instanceof Page && $object->getType() == PageEnum::HOME->value;
        }

        $parent = $this->paragraphService->getEntityParent($paragraph);

        return $parent->value->getId() == $object->getId();
    }
}
