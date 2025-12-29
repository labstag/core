<?php

namespace Labstag\Data;

use DateTime;
use Labstag\Entity\Page;
use Labstag\Entity\Post;
use Labstag\Enum\PageEnum;
use Labstag\Shortcode\PostUrlShortcode;
use Override;
use Spatie\SchemaOrg\Schema;
use Symfony\Component\Routing\RouterInterface;

class PostData extends PageData implements DataInterface
{
    #[Override]
    public function generateSlug(object $entity): array
    {
        $page  = $this->entityManager->getRepository(Page::class)->findOneBy(
            [
                'type' => PageEnum::POSTS->value,
            ]
        );

        $slug = parent::generateSlug($page);
        $slug['slug'] .= '/' . $entity->getSlug();

        return $slug;
    }

    public function getDefaultImage(object $entity): string
    {
        return $entity->getImg();
    }

    #[Override]
    public function getEntity(?string $slug): object
    {
        return $this->getEntityBySlugPost($slug);
    }

    public function getJsonLd(object $entity): object
    {
        $blogPosting = Schema::BlogPosting();
        $blogPosting->headline($entity->getTitle());

        $resume      = $entity->getResume();
        $clean       = trim(html_entity_decode(strip_tags($resume), ENT_QUOTES | ENT_HTML5, 'UTF-8'));
        $blogPosting->description($clean);

        $img = $this->siteService->asset($entity, 'img', true, true);
        if ('' !== $img) {
            $blogPosting->image($img);
        }

        $blogPosting->author(Schema::person()->name($entity->getRefuser()->getUsername()));

        if ($entity->getCreatedAt() instanceof DateTime) {
            $blogPosting->datePublished($entity->getCreatedAt()->format('c'));
        }

        if ($entity->getUpdatedAt() instanceof DateTime) {
            $blogPosting->dateModified($entity->getUpdatedAt()->format('c'));
        }

        $params = $this->slugService->forEntity($entity);
        $blogPosting->mainEntityOfPage(
            Schema::webPage()->id($this->router->generate('front', $params, RouterInterface::ABSOLUTE_URL))
        );

        return $blogPosting;
    }

    #[Override]
    public function getShortCodes(): array
    {
        return [PostUrlShortcode::class];
    }

    #[Override]
    public function getTitle(object $entity): string
    {
        return $entity->getTitle();
    }

    #[Override]
    public function getTitleMeta(object $entity): string
    {
        return $this->getTitle($entity);
    }

    #[Override]
    public function match(?string $slug): bool
    {
        $page = $this->getEntityBySlugPost($slug);

        return $page instanceof Post;
    }

    #[Override]
    public function placeholder(): string
    {
        $placeholder = $this->globalPlaceholder('Post');
        if ('' !== $placeholder) {
            return $placeholder;
        }

        return $this->configPlaceholder();
    }

    #[Override]
    public function supportsAsset(object $entity): bool
    {
        return $entity instanceof Post;
    }

    #[Override]
    public function supportsData(object $entity): bool
    {
        return $entity instanceof Post;
    }

    #[Override]
    public function supportsJsonLd(object $entity): bool
    {
        return $entity instanceof Post;
    }

    #[Override]
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
