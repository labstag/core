<?php

namespace Labstag\Block;

use EasyCorp\Bundle\EasyAdminBundle\Field\CollectionField;
use Labstag\Entity\Block;
use Labstag\Form\LinkType;
use Labstag\Lib\BlockLib;
use Override;
use Symfony\Component\Translation\TranslatableMessage;

class LinksBlock extends BlockLib
{
    #[Override]
    public function content(string $view, Block $block)
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
    public function generate(Block $block, array $data)
    {
        $links = $block->getLinks();
        if (0 == count($links)) {
            $this()->setShow($block, false);

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
}
