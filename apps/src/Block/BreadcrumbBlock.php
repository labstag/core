<?php

namespace Labstag\Block;

use Labstag\Block\Traits\CacheableTrait;
use Labstag\Entity\Block;
use Labstag\Entity\BreadcrumbBlock as EntityBreadcrumbBlock;
use Labstag\Entity\Page;
use Labstag\Enum\PageEnum;
use Override;
use ReflectionClass;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class BreadcrumbBlock extends BlockAbstract
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
        $params  = $this->getParamsAttributes($request);

        if ([] === $urls) {
            $this->setShow($block, false);

            return;
        }

        $this->setData(
            $block,
            [
                'params' => $params,
                'urls'   => $urls,
                'block'  => $block,
                'data'   => $data,
            ]
        );
    }

    public function getClass(): string
    {
        return EntityBreadcrumbBlock::class;
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

    private function getParamsAttributes(Request $request): array
    {
        $params = $request->attributes->get('_route_params', []);
        if (isset($params['slug'])) {
            unset($params['slug']);
        }

        if (isset($params['page']) && 1 == $params['page']) {
            unset($params['page']);
        }

        $query = $request->query->all();
        foreach ($query as $key => $value) {
            if (!in_array($key, ['title', 'categories', 'sagas', 'year', 'order', 'orderby'])) {
                continue;
            }

            $params[$key] = $value;
        }

        return $params;
    }

    /**
     * @return mixed[]
     */
    private function setBreadcrumb(string $slug): array
    {
        $cacheKey = 'breadcrumb_' . md5($slug);

        return $this->getCached(
            $cacheKey,
            function () use ($slug): array {
                $urls        = [];
                $currentSlug = $slug;

                while ('' !== $currentSlug) {
                    foreach ($this->dataLibs as $dataLib) {
                        $classe = new ReflectionClass($dataLib);
                        if ($classe->hasMethod('getTitle') && $classe->hasMethod('match') && $dataLib->match(
                            $currentSlug
                        )
                        ) {
                            $entity = $dataLib->getEntity($currentSlug);
                            $urls[] = [
                                'title' => $dataLib->getTitle($entity),
                                'url'   => $currentSlug,
                            ];
                        }
                    }

                    if ('0' === $currentSlug) {
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
