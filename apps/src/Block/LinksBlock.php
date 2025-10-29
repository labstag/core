<?php

namespace Labstag\Block;

use EasyCorp\Bundle\EasyAdminBundle\Contracts\Field\FieldInterface;
use EasyCorp\Bundle\EasyAdminBundle\Field\CollectionField;
use Generator;
use Labstag\Entity\Block;
use Labstag\Form\LinkType;
use Override;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Translation\TranslatableMessage;

class LinksBlock extends BlockAbstract
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

        $this->logger->debug(
            'Starting links block generation',
            [
                'block_id' => $block->getId(),
            ]
        );

        $links = $this->correctionLinks($block);
        if ([] === $links) {
            $this->logger->debug(
                'No valid links found',
                [
                    'block_id' => $block->getId(),
                ]
            );
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

        foreach ($links as $link) {
            $url = $this->shortCodeService->getContent($link->getUrl());
            if (null === $url) {
                continue;
            }

            $link->setUrl($url);

            $data[] = $link;
        }

        return $data;
    }
}
