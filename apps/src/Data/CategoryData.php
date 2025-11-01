<?php

namespace Labstag\Data;

use Labstag\Entity\Category;
use Labstag\Entity\Page;
use Labstag\Enum\PageEnum;
use Symfony\Component\Translation\TranslatableMessage;

class CategoryData extends DataAbstract implements DataInterface
{
    #[\Override]
    public function asset(mixed $entity, string $field): string
    {
        return '';
    }

    public function generateSlug(object $entity): string
    {
        $type             = $entity->getType();
        $entityRepository = $this->entityManager->getRepository(Page::class);
        $page             = match ($type) {
            'movie' => $entityRepository->findOneBy(
                [
                    'type' => PageEnum::MOVIES->value,
                ]
            ),
            'post' => $entityRepository->findOneBy(
                [
                    'type' => PageEnum::POSTS->value,
                ]
            ),
            'serie' => $entityRepository->findOneBy(
                [
                    'type' => PageEnum::SERIES->value,
                ]
            ),
            'story' => $entityRepository->findOneBy(
                [
                    'type' => PageEnum::STORIES->value,
                ]
            ),
            default => null,
        };

        if (!$page instanceof Page) {
            return '';
        }

        return match ($type) {
            'movie' => $page->getSlug() . '/category-' . $entity->getSlug(),
            'post'  => $page->getSlug() . '/category-' . $entity->getSlug(),
            'serie' => $page->getSlug() . '/category-' . $entity->getSlug(),
            'story' => $page->getSlug() . '/category-' . $entity->getSlug(),
            default => '',
        };
    }

    public function getEntity(string $slug): object
    {
        return $this->getEntityBySlug($slug);
    }

    public function getTitle(object $entity): string
    {
        $request  = $this->requestStack->getCurrentRequest();
        $slug     = $request->attributes->get('slug');
        $category = $this->getCategoryBySlug($slug);

        $params = [
            '%category%' => $category->getTitle(),
        ];

        return $this->translator->trans(new TranslatableMessage('Category %category%'), $params);
    }

    public function match(string $slug): bool
    {
        $page = $this->getEntityBySlug($slug);

        return $page instanceof Page;
    }

    public function placeholder(): string
    {
        return '';
    }

    public function supportsAsset(object $entity): bool
    {
        return false;
    }

    public function supportsData(object $entity): bool
    {
        return $entity instanceof Category;
    }

    public function supportsShortcode(string $className): bool
    {
        return false;
    }

    protected function getCategoryBySlug(string $slug): ?object
    {
        if (0 === substr_count($slug, '/')) {
            return null;
        }

        $slugSecond = basename($slug);
        $slugFirst  = dirname($slug);

        $page = $this->entityManager->getRepository(Page::class)->findOneBy(
            ['slug' => $slugFirst]
        );
        if (!$page instanceof Page) {
            return null;
        }

        if (0 === substr_count($slugSecond, 'category-')) {
            return null;
        }

        $type = match ($page->getType()) {
            PageEnum::MOVIES->value  => 'movie',
            PageEnum::POSTS->value   => 'post',
            PageEnum::SERIES->value  => 'serie',
            PageEnum::STORIES->value => 'story',
            default                  => null,
        };

        if (is_null($type)) {
            return null;
        }

        $slugSecond = str_replace('category-', '', $slugSecond);

        return $this->entityManager->getRepository(Category::class)->findOneBy(
            [
                'type' => $type,
                'slug' => $slugSecond,
            ]
        );
    }

    protected function getEntityBySlug(string $slug): ?object
    {
        if (0 === substr_count($slug, '/')) {
            return null;
        }

        $slugSecond = basename($slug);
        $slugFirst  = dirname($slug);

        $page = $this->entityManager->getRepository(Page::class)->findOneBy(
            ['slug' => $slugFirst]
        );
        if (!$page instanceof Page) {
            return null;
        }

        if (0 === substr_count($slugSecond, 'category-')) {
            return null;
        }

        $type = match ($page->getType()) {
            PageEnum::MOVIES->value  => 'movie',
            PageEnum::POSTS->value   => 'post',
            PageEnum::SERIES->value  => 'serie',
            PageEnum::STORIES->value => 'story',
            default                  => null,
        };

        if (is_null($type)) {
            return null;
        }

        $slugSecond = str_replace('category-', '', $slugSecond);
        $category   = $this->entityManager->getRepository(Category::class)->findOneBy(
            [
                'type' => $type,
                'slug' => $slugSecond,
            ]
        );
        if (!$category instanceof Category) {
            return null;
        }

        return $page;
    }
}
