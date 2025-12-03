<?php

namespace Labstag\Data;

use Labstag\Entity\Category;
use Labstag\Entity\GameCategory;
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
    public function generateSlug(object $entity): array
    {
        $page = $this->getPage($entity::class);

        if (!$page instanceof Page) {
            return ['slug' => ''];
        }

        return match ($entity::class) {
            GameCategory::class => [
                'slug'       => $page->getSlug(),
                'categories' => $entity->getSlug(),
            ],
            MovieCategory::class => [
                'slug'       => $page->getSlug(),
                'categories' => $entity->getSlug(),
            ],
            PostCategory::class  => [
                'slug'       => $page->getSlug(),
                'categories' => $entity->getSlug(),
            ],
            SerieCategory::class => [
                'slug'       => $page->getSlug(),
                'categories' => $entity->getSlug(),
            ],
            StoryCategory::class => [
                'slug'       => $page->getSlug(),
                'categories' => $entity->getSlug(),
            ],
            default              => ['slug' => ''],
        };
    }

    #[\Override]
    public function getEntity(?string $slug): object
    {
        return $this->getEntityBySlugCategory($slug);
    }

    #[\Override]
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

    #[\Override]
    public function match(?string $slug): bool
    {
        $page = $this->getEntityBySlugCategory($slug);

        return $page instanceof Page;
    }

    #[\Override]
    public function supportsData(object $entity): bool
    {
        return $entity instanceof Category;
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
            PageEnum::GAMES->value   => GameCategory::class,
            PageEnum::MOVIES->value  => MovieCategory::class,
            PageEnum::POSTS->value   => PostCategory::class,
            PageEnum::SERIES->value  => SerieCategory::class,
            PageEnum::STORIES->value => StoryCategory::class,
            default                  => null,
        };
    }

    protected function getEntityBySlugCategory(?string $slug): ?object
    {
        if (0 === substr_count((string) $slug, '/')) {
            return null;
        }

        $slugSecond = basename((string) $slug);
        $slugFirst  = dirname((string) $slug);

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
            GameCategory::class => $entityRepository->findOneBy(
                [
                    'type' => PageEnum::GAMES->value,
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
