<?php

namespace Labstag\Block;

use EasyCorp\Bundle\EasyAdminBundle\Field\CollectionField;
use Labstag\Entity\Block;
use Labstag\Entity\Page;
use Labstag\Entity\Post;
use Labstag\Entity\Story;
use Labstag\Form\LinkType;
use Labstag\Lib\BlockLib;
use Override;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Translation\TranslatableMessage;

class LinksBlock extends BlockLib
{
    #[Override]
    public function content(string $view, Block $block): ?Response
    {
        if (!$this->isShow($block)) {
            return null;
        }

        return $this->render(
            $view,
            $this->getData($block)
        );
    }

    #[Override]
    public function generate(Block $block, array $data, bool $disable): void
    {
        unset($disable);
        $links = $this->correctionLinks($block);
        if (0 == count($links)) {
            $this->setShow($block, false);

            return;
        }

        $this->setData(
            $block,
            [
                'links' => $links,
                'block' => $block,
                'data'  => $data,
            ]
        );
    }

    #[Override]
    public function getFields(Block $block, $pageName): iterable
    {
        unset($block, $pageName);

        $collectionField = CollectionField::new('links', new TranslatableMessage('Links'));
        $collectionField->allowAdd(true);
        $collectionField->allowDelete(true);
        $collectionField->setEntryType(LinkType::class);
        yield $collectionField;
    }

    #[Override]
    public function getName(): string
    {
        return 'Links';
    }

    #[Override

    ]
    public function getType(): string
    {
        return 'links';
    }

    /**
     * @return mixed[]
     */
    private function correctionLinks(Block $block): array
    {
        $links = $block->getLinks();
        $data  = [];
        foreach ($links as $link) {
            $entity = clone $link;
            $entity->setUrl($this->correctionUrl($entity->getUrl()));

            $data[] = $entity;
        }

        return $data;
    }

    private function correctionUrl($url): ?string
    {
        // Regex \[page:(.*)]
        $url = preg_replace_callback(
            '/\[page:(.*?)]/',
            fn ($matches): ?string =>
            // Assuming you have a method to get the actual URL from the page identifier
                $this->getEntityUrl(Page::class, $matches[1]),
            (string) $url
        );
        $url = preg_replace_callback(
            '/\[post:(.*?)]/',
            fn ($matches): ?string =>
            // Assuming you have a method to get the actual URL from the page identifier
                $this->getEntityUrl(Post::class, $matches[1]),
            (string) $url
        );

        return preg_replace_callback(
            '/\[story:(.*?)]/',
            fn ($matches): ?string =>
            // Assuming you have a method to get the actual URL from the page identifier
                $this->getEntityUrl(Story::class, $matches[1]),
            (string) $url
        );
    }

    private function getEntityUrl(string $entity, string $id): ?string
    {
        $data = $this->getRepository($entity)->find($id);
        if (is_null($data)) {
            return null;
        }

        $slug = $this->siteService->getSlugByEntity($data);

        return $this->router->generate('front', ['slug' => $slug]);
    }
}
