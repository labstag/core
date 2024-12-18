<?php

namespace Labstag\Block;

use Labstag\Entity\Block;
use Labstag\Entity\Page;
use Labstag\Lib\BlockLib;
use Override;

class BreadcrumbBlock extends BlockLib
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
        if ($data['entity'] instanceof Page && 'home' == $data['entity']->getType()) {
            $this->setShow($block, false);

            return;
        }

        $request = $this->requestStack->getCurrentRequest();
        $slug    = $request->attributes->get('slug');
        $urls    = $this->setBreadcrumb([], $slug);
        $urls    = array_reverse($urls);

        if (0 == count($urls)) {
            $this->setShow($block, false);

            return;
        }

        $this->setData(
            $block,
            [
                'urls'  => $urls,
                'block' => $block,
                'data'  => $data,
            ]
        );
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
        return 'Breadcrumb';
    }

    #[Override

    ]
    public function getType(): string
    {
        return 'breadcrumb';
    }

    private function setBreadcrumb($urls, $slug)
    {
        $entity = $this->siteService->getEntityBySlug($slug);
        if (is_object($entity)) {
            $urls[] = [
                'title' => $entity->getTitle(),
                'url'   => $slug,
            ];
        }

        if ('' == $slug) {
            return $urls;
        }

        if (0 != substr_count((string) $slug, '/')) {
            $slug = substr((string) $slug, 0, strrpos((string) $slug, '/'));

            return $this->setBreadcrumb($urls, $slug);
        }

        return $this->setBreadcrumb($urls, '');
    }
}
