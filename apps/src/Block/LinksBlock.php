<?php

namespace Labstag\Block;

use EasyCorp\Bundle\EasyAdminBundle\Contracts\Field\FieldInterface;
use EasyCorp\Bundle\EasyAdminBundle\Field\CollectionField;
use Generator;
use Labstag\Block\Abstract\BlockLib;
use Labstag\Entity\Block;
use Labstag\Form\LinkType;
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

        foreach ($links as $row) {
            $link         = clone $row;
            $processedUrl = $this->linkUrlProcessor->processUrl($link->getUrl());

            // If processedUrl is an object (entity), check if it's enabled
            if (is_object($processedUrl)) {
                if (!$processedUrl->isEnable()) {
                    continue;
                }

                $link->setUrl(
                    $this->router->generate(
                        'front',
                        [
                            'slug' => $this->slugService->forEntity($processedUrl),
                        ]
                    )
                );
                $data[] = $link;

                continue;
            }

            // It's a regular URL string
            $link->setUrl($processedUrl);

            $data[] = $link;
        }

        return $data;
    }
}
