<?php

namespace Labstag\Block;

use EasyCorp\Bundle\EasyAdminBundle\Contracts\Field\FieldInterface;
use EasyCorp\Bundle\EasyAdminBundle\Field\CollectionField;
use Generator;
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

        return $this->render($view, $this->getData($block));
    }

    /**
     * @param mixed[] $data
     */
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

    /**
     * @return Generator<FieldInterface>
     */
    #[Override]
    public function getFields(Block $block, string $pageName): mixed
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

    #[Override]
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
        foreach ($links as $row) {
            $link   = clone $row;
            $entity = $this->getEntityByTagUrl($link->getUrl());
            if (!is_object($entity)) {
                $data[] = $link;
                continue;
            }

            if (!$entity->isEnable()) {
                continue;
            }

            $link->setUrl(
                $this->router->generate(
                    'front',
                    [
                        'slug' => $this->siteService->getSlugByEntity($entity),
                    ]
                )
            );

            $data[] = $link;
        }

        return $data;
    }

    private function getEntityByTagUrl(string $object): mixed
    {
        preg_match('/\[pageurl:(.*?)]/', $object, $matchespage);
        preg_match('/\[posturl:(.*?)]/', $object, $matchespost);
        preg_match('/\[storyurl:(.*?)]/', $object, $matchesstory);

        if (isset($matchespage[1])) {
            return $this->getEntity(Page::class, $matchespage[1]);
        }

        if (isset($matchespost[1])) {
            return $this->getEntity(Post::class, $matchespost[1]);
        }

        if (isset($matchesstory[1])) {
            return $this->getEntity(Story::class, $matchesstory[1]);
        }

        return $object;
    }

    private function getEntity(string $entity, string $id): ?object
    {
        $data = $this->getRepository($entity)->find($id);
        if (is_null($data)) {
            return null;
        }

        return $data;
    }
}
