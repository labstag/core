<?php

namespace Labstag\Block;

use EasyCorp\Bundle\EasyAdminBundle\Contracts\Field\FieldInterface;
use EasyCorp\Bundle\EasyAdminBundle\Field\CollectionField;
use EasyCorp\Bundle\EasyAdminBundle\Field\FormField;
use Generator;
use Labstag\Entity\Block;
use Labstag\Entity\LinksBlock as EntityLinksBlock;
use Labstag\Form\Block\LinkType;
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
        if (!$block instanceof EntityLinksBlock) {
            return;
        }

        unset($disable);

        $this->logger->debug(
            'Starting links block generation',
            [
                'block_id' => $block->getId(),
            ]
        );

        $links  = $block->getLinks();
        if (!is_array($links) || [] === $links) {
            $this->logger->debug(
                'No valid links found',
                [
                    'block_id' => $block->getId(),
                ]
            );
            $this->setShow($block, false);

            return;
        }

        $links = $this->correctionLinks($links);
        $this->setData(
            $block,
            [
                'links' => $links,
                'block' => $block,
                'data'  => $data,
            ]
        );
    }

    public function getClass(): string
    {
        return EntityLinksBlock::class;
    }

    /**
     * @return Generator<FieldInterface>
     */
    #[Override]
    public function getFields(Block $block, string $pageName): mixed
    {
        unset($block, $pageName);

        yield FormField::addColumn(12);
        $collectionField = CollectionField::new('links', new TranslatableMessage('Links'));
        $collectionField->setEntryToStringMethod(
            function ($link): string {
                unset($link);

                $translatableMessage = new TranslatableMessage('Link');

                return $this->translator->trans(
                    $translatableMessage->getMessage(),
                    $translatableMessage->getParameters()
                );
            }
        );
        $collectionField->setFormTypeOption(
            'attr',
            ['data-controller' => 'sortable']
        );
        $collectionField->setEntryType(LinkType::class);
        yield $collectionField;
    }

    #[Override]
    public function getName(): TranslatableMessage
    {
        return new TranslatableMessage('Links');
    }

    #[Override]
    public function getType(): string
    {
        return 'links';
    }

    #[Override]
    public function update(Block $block): void
    {
        $this->updateBlockLinks($block);
    }

    /**
     * @return mixed[]
     */
    private function correctionLinks(array $links): array
    {
        $data  = [];
        foreach ($links as $link) {
            if (isset($link['links'])) {
                $link['links'] = $this->correctionLinks($link['links']);
                $data[]        = $link;
                continue;
            }

            $url = $this->shortCodeService->getContent($link['url']);
            if (null === $url) {
                continue;
            }

            $data[] = $link;
        }

        return $data;
    }

    private function updateBlockLinks(Block $block): void
    {
        if (!$block instanceof EntityLinksBlock) {
            return;
        }

        $oldskils = $block->getLinks();
        if (!is_array($oldskils)) {
            return;
        }

        $skills = [];
        foreach ($oldskils as $key => $skill) {
            $position          = (!isset($skill['position']) || is_null(
                $skill['position']
            )) ? $key : $skill['position'];
            $skill['position'] = $position;
            $skills[$position] = $skill;
        }

        ksort($skills);

        $block->setLinks($skills);
    }
}
