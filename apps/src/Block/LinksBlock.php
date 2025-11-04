<?php

namespace Labstag\Block;

use EasyCorp\Bundle\EasyAdminBundle\Contracts\Field\FieldInterface;
use EasyCorp\Bundle\EasyAdminBundle\Field\Field;
use EasyCorp\Bundle\EasyAdminBundle\Field\FormField;
use Generator;
use Labstag\Entity\Block;
use Labstag\Entity\LinksBlock as EntityLinksBlock;
use Labstag\Form\Block\DataLinkType;
use Override;
use Symfony\Component\HttpFoundation\Response;

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

        $data  = $block->getData();
        $links = $data['links'] ?? [];
        if (0 === count($links)) {
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
        $fieldData = Field::new('data', 'Bloc de données');
        $fieldData->setFormType(DataLinkType::class);
        yield $fieldData;
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
    private function correctionLinks(array $links): array
    {
        $data  = [];

        foreach ($links as $link) {
            $url = $this->shortCodeService->getContent($link['url']);
            if (null === $url) {
                continue;
            }

            $link['url'] = $url;

            $data[] = $link;
        }

        return $data;
    }
}
