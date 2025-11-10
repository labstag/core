<?php

namespace Labstag\Data;

use Labstag\Entity\Page;
use Labstag\Entity\Post;
use Labstag\Enum\PageEnum;
use Labstag\Shortcode\PostUrlShortcode;

class PostData extends PageData implements DataInterface
{
    #[\Override]
    public function generateSlug(object $entity): string
    {
        $page  = $this->entityManager->getRepository(Page::class)->findOneBy(
            [
                'type' => PageEnum::POSTS->value,
            ]
        );

        return parent::generateSlug($page) . '/' . $entity->getSlug();
    }

    #[\Override]
    public function getEntity(?string $slug): object
    {
        return $this->getEntityBySlugPost($slug);
    }

    #[\Override]
    public function getShortCodes(): array
    {
        return [PostUrlShortcode::class];
    }

    #[\Override]
    public function getTitle(object $entity): string
    {
        return $entity->getTitle();
    }

    #[\Override]
    public function getTitleMeta(object $entity): string
    {
        return $this->getTitle($entity);
    }

    #[\Override]
    public function match(?string $slug): bool
    {
        $page = $this->getEntityBySlugPost($slug);

        return $page instanceof Post;
    }

    #[\Override]
    public function placeholder(): string
    {
        $placeholder = $this->globalPlaceholder('Post');
        if ('' !== $placeholder) {
            return $placeholder;
        }

        return $this->configPlaceholder();
    }

    #[\Override]
    public function supportsAsset(object $entity): bool
    {
        return $entity instanceof Post;
    }

    #[\Override]
    public function supportsData(object $entity): bool
    {
        return $entity instanceof Post;
    }

    #[\Override]
    public function supportsShortcode(string $className): bool
    {
        return Post::class === $className;
    }

    protected function getEntityBySlugPost(?string $slug): ?object
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

        if ($page->getType() != PageEnum::POSTS->value) {
            return null;
        }

        return $this->entityManager->getRepository(Post::class)->findOneBy(
            ['slug' => $slugSecond]
        );
    }
}
