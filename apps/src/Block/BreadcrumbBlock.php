<?php

namespace Labstag\Block;

use Labstag\Entity\Block;
use Labstag\Entity\Page;
use Labstag\Enum\PageEnum;
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

    /**
     * @param mixed[] $data
     */
    #[Override]
    public function generate(Block $block, array $data, bool $disable): void
    {
        unset($disable);
        if ($data['entity'] instanceof Page && PageEnum::HOME->value == $data['entity']->getType()) {
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
    public function getName(): string
    {
        return 'Breadcrumb';
    }

    #[Override]
    public function getType(): string
    {
        return 'breadcrumb';
    }

    /**
     * @param mixed[] $urls
     *
     * @return mixed[]
     */
    private function setBreadcrumb(array $urls, string $slug): array
    {
        $entity = $this->slugService->getEntityBySlug($slug);
        if (is_object($entity)) {
            $urls[] = [
                'title' => $entity->getTitle(),
                'url'   => $slug,
            ];
        }

        if ('' === $slug) {
            return $urls;
        }

        if (0 != substr_count($slug, '/')) {
            $slug = substr($slug, 0, strrpos($slug, '/'));

            return $this->setBreadcrumb($urls, $slug);
        }

        return $this->setBreadcrumb($urls, '');
    }
}
