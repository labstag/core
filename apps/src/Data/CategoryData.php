<?php

namespace Labstag\Data;

use Labstag\Entity\Category;
use Labstag\Entity\MovieCategory;
use Labstag\Entity\Page;
use Labstag\Entity\PostCategory;
use Labstag\Entity\SerieCategory;
use Labstag\Entity\StoryCategory;
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
        $page = $this->getPage($entity::class);

        if (!$page instanceof Page) {
            return '';
        }

        return match ($entity::class) {
            MovieCategory::class => $page->getSlug() . '/category-' . $entity->getSlug(),
            PostCategory::class  => $page->getSlug() . '/category-' . $entity->getSlug(),
            SerieCategory::class => $page->getSlug() . '/category-' . $entity->getSlug(),
            StoryCategory::class => $page->getSlug() . '/category-' . $entity->getSlug(),
            default              => '',
        };
    }

    public function getEntity(string $slug): object
    {
        return $this->getEntityBySlug($slug);
    }

    public function getTitle(object $entity): string
    {
        unset($entity);
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

        $typeclass = $this->getClass($page->getType());
        if (is_null($typeclass)) {
            return null;
        }

        $slugSecond = str_replace('category-', '', $slugSecond);

        return $this->entityManager->getRepository($typeclass)->findOneBy(
            ['slug' => $slugSecond]
        );
    }

    protected function getClass(string $type): ?string
    {
        return match ($type) {
            PageEnum::MOVIES->value  => MovieCategory::class,
            PageEnum::POSTS->value   => PostCategory::class,
            PageEnum::SERIES->value  => SerieCategory::class,
            PageEnum::STORIES->value => StoryCategory::class,
            default                  => null,
        };
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

        $typeclass = $this->getClass($page->getType());
        if (is_null($typeclass)) {
            return null;
        }

        $slugSecond = str_replace('category-', '', $slugSecond);
        $category   = $this->entityManager->getRepository($typeclass)->findOneBy(
            ['slug' => $slugSecond]
        );
        if (!$category instanceof Category) {
            return null;
        }

        return $page;
    }

    protected function getPage(string $entity): ?Page
    {
        $entityRepository = $this->entityManager->getRepository(Page::class);

        return match ($entity) {
            MovieCategory::class => $entityRepository->findOneBy(
                [
                    'type' => PageEnum::MOVIES->value,
                ]
            ),
            PostCategory::class => $entityRepository->findOneBy(
                [
                    'type' => PageEnum::POSTS->value,
                ]
            ),
            SerieCategory::class => $entityRepository->findOneBy(
                [
                    'type' => PageEnum::SERIES->value,
                ]
            ),
            StoryCategory::class => $entityRepository->findOneBy(
                [
                    'type' => PageEnum::STORIES->value,
                ]
            ),
            default => null,
        };
    }
}
