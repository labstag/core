<?php

namespace Labstag\Data;

use Override;
use Labstag\Entity\Page;
use Labstag\Entity\PostTag;
use Labstag\Entity\StoryTag;
use Labstag\Entity\Tag;
use Labstag\Enum\PageEnum;
use Symfony\Component\Translation\TranslatableMessage;

class TagData extends DataAbstract implements DataInterface
{
    #[Override]
    public function generateSlug(object $entity): array
    {
        $entityRepository = $this->entityManager->getRepository(Page::class);
        $page             = match ($entity::class) {
            PostTag::class => $entityRepository->findOneBy(
                [
                    'type' => PageEnum::POSTS->value,
                ]
            ),
            StoryTag::class => $entityRepository->findOneBy(
                [
                    'type' => PageEnum::STORIES->value,
                ]
            ),
            default => null,
        };

        if (!$page instanceof Page) {
            return ['slug' => ''];
        }

        return match ($entity::class) {
            PostTag::class  => [
                'slug' => $page->getSlug(),
                'tag'  => $entity->getSlug(),
            ],
            StoryTag::class => [
                'slug' => $page->getSlug(),
                'tag'  => $entity->getSlug(),
            ],
            default         => ['slug' => ''],
        };
    }

    #[Override]
    public function getEntity(?string $slug): object
    {
        return $this->getEntityBySlugTag($slug);
    }

    #[Override]
    public function getTitle(object $entity): string
    {
        unset($entity);
        $request  = $this->requestStack->getCurrentRequest();
        $slug     = $request->attributes->get('slug');
        $tag      = $this->getTagBySlug($slug);

        $params = [
            '%tag%' => $tag->getTitle(),
        ];

        return $this->translator->trans(new TranslatableMessage('Tag %tag%'), $params);
    }

    #[Override]
    public function match(?string $slug): bool
    {
        $page = $this->getEntityBySlugTag($slug);

        return $page instanceof Page;
    }

    #[Override]
    public function supportsData(object $entity): bool
    {
        return $entity instanceof Tag;
    }

    protected function getClass(string $type): ?string
    {
        return match ($type) {
            PageEnum::POSTS->value   => PostTag::class,
            PageEnum::STORIES->value => StoryTag::class,
            default                  => null,
        };
    }

    protected function getEntityBySlugTag(?string $slug): ?object
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

        if (0 === substr_count($slugSecond, 'tag-')) {
            return null;
        }

        $typeclass = $this->getClass($page->getType());
        if (is_null($typeclass)) {
            return null;
        }

        $slugSecond = str_replace('tag-', '', $slugSecond);
        $tag        = $this->entityManager->getRepository($typeclass)->findOneBy(
            ['slug' => $slugSecond]
        );
        if (!$tag instanceof Tag) {
            return null;
        }

        return $page;
    }

    protected function getTagBySlug(string $slug): ?object
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

        if (0 === substr_count($slugSecond, 'tag-')) {
            return null;
        }

        $typeclass = $this->getClass($page->getType());
        if (is_null($typeclass)) {
            return null;
        }

        $slugSecond = str_replace('tag-', '', $slugSecond);

        return $this->entityManager->getRepository($typeclass)->findOneBy(
            ['slug' => $slugSecond]
        );
    }
}
