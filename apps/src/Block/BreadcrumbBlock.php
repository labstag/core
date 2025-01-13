<?php

namespace Labstag\Block;

use Labstag\Entity\Block;
use Labstag\Entity\Page;
use Labstag\Lib\BlockLib;
use Override;
use Symfony\Component\HttpFoundation\Response;

class BreadcrumbBlock extends BlockLib
{
    #[Override]
    public function content(string $view, Block $block): ?Response
    {
        if (!$this->isShow($block)) {
            return null;
        }

        return $this->render($view, $this->getData($block));
    }

    #[Override]
    public function generate(Block $block, array $data, bool $disable): void
    {
        unset($disable);
        if ($data['entity'] instanceof Page && $data['entity']->getType() == 'home') {
            $this->setShow($block, false);

            return;
        }

        $request = $this->requestStack->getCurrentRequest();
        $slug = $request->attributes->get('slug');
        $urls = $this->setBreadcrumb([], $slug);
        $urls = array_reverse($urls);

        if (count($urls) == 0) {
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
    public function getFields(Block $block, string $pageName): iterable
    {
        unset($block, $pageName);

        return [];
    }

    #[Override]
    public function getName(): string
    {
        return 'Breadcrumb';
    }

    #[Override]
    public function getType(): string
    {
        return 'breadcrumb';
    }

    private function setBreadcrumb(array $urls, string $slug): array
    {
        $entity = $this->siteService->getEntityBySlug($slug);
        if (is_object($entity)) {
            $urls[] = [
                'title' => $entity->getTitle(),
                'url'   => $slug,
            ];
        }

        if ($slug === '') {
            return $urls;
        }

        if (substr_count($slug, '/') != 0) {
            $slug = substr($slug, 0, strrpos($slug, '/'));

            return $this->setBreadcrumb($urls, $slug);
        }

        return $this->setBreadcrumb($urls, '');
    }
}
