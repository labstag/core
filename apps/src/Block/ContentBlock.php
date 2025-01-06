<?php

namespace Labstag\Block;

use Labstag\Entity\Block;
use Labstag\Entity\Page;
use Labstag\Lib\BlockLib;
use Override;

class ContentBlock extends BlockLib
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
    public function generate(Block $block, array $data, bool $disable)
    {
        $paragraphs = $data['paragraphs'];
        if (0 == count($paragraphs)) {
            $this->setShow($block, false);

            return;
        }

        $paragraphs = $this->paragraphService->generate(
            $paragraphs,
            $data,
            $disable
        );

        $contents = $this->paragraphService->getContents($paragraphs);
        $this->setHeader($block, $contents->header);
        $this->setFooter($block, $contents->footer);

        $tab = [
            'block'      => $block,
            'data'       => $data,
            'paragraphs' => $paragraphs,
        ];

        if (!($data['entity'] instanceof Page && 'home' == $data['entity']->getType())) {
            $aside = $this->getAside($data);
            if (!is_null($aside)) {
                $tab['aside'] = $aside;
            }
        }

        $this->setData($block, $tab);
    }

    #[Override]
    public function getFields(Block $block, $pageName): iterable
    {
        unset($block, $pageName);

        return [];
    }

    #[Override]
    public function getName(): string
    {
        return 'Content';
    }

    #[Override]
    public function getType(): string
    {
        return 'content';
    }

    private function getAside($data)
    {
        unset($data);
        // TODO: Implement getAside() method.
    }
}
