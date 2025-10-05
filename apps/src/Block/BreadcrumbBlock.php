<?php

namespace Labstag\Block;

use Labstag\Block\Abstract\BlockLib;
use Labstag\Block\Traits\CacheableTrait;
use Labstag\Entity\Block;
use Labstag\Entity\Page;
use Labstag\Enum\PageEnum;
use Override;
use Symfony\Component\HttpFoundation\Response;

class BreadcrumbBlock extends BlockLib
{
    use CacheableTrait;

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
        $urls    = $this->setBreadcrumb($slug);

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
     * @return mixed[]
     */
    private function setBreadcrumb(string $slug): array
    {
        $cacheKey = 'breadcrumb_' . md5($slug);

        return $this->getCached(
            $cacheKey,
            function () use ($slug) {
                $urls        = [];
                $currentSlug = $slug;

                while ('' !== $currentSlug) {
                    $entity = $this->slugService->getEntityBySlug($currentSlug);
                    if (is_object($entity)) {
                        $urls[] = [
                            'title' => $entity->getTitle(),
                            'url'   => $currentSlug,
                        ];
                    }

                    if ($currentSlug === '0') {
                        break;
                    }

                    $currentSlug = (0 < substr_count($currentSlug, '/')) ? substr(
                        $currentSlug,
                        0,
                        strrpos($currentSlug, '/')
                    ) : '';
                }

                return array_reverse($urls);
            }
        );
    }
}
