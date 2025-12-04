<?php

namespace Labstag\Data;

use Labstag\Entity\Chapter;
use Labstag\Entity\Page;
use Labstag\Entity\Story;
use Labstag\Enum\PageEnum;
use Labstag\Shortcode\StoryUrlShortcode;
use Override;
use Spatie\SchemaOrg\Schema;
use Symfony\Component\Routing\RouterInterface;

class StoryData extends PageData implements DataInterface
{
    #[Override]
    public function generateSlug(object $entity): array
    {
        $page  = $this->entityManager->getRepository(Page::class)->findOneBy(
            [
                'type' => PageEnum::STORIES->value,
            ]
        );

        $slug = parent::generateSlug($page);
        $slug['slug'] .= '/' . $entity->getSlug();

        return $slug;
    }

    #[Override]
    public function getEntity(?string $slug): object
    {
        return $this->getEntityBySlugStory($slug);
    }

    public function getJsonLd(object $entity): object
    {
        $creativeWorkSeries = Schema::creativeWorkSeries();
        $creativeWorkSeries->name($entity->getTitle());

        $resume      = $entity->getResume();
        $clean       = trim(html_entity_decode(strip_tags($resume), ENT_QUOTES | ENT_HTML5, 'UTF-8'));
        $creativeWorkSeries->description($clean);
        $params = $this->slugService->forEntity($entity);
        $creativeWorkSeries->url($this->router->generate('front', $params, RouterInterface::ABSOLUTE_URL));
        $chapters = [];
        foreach ($entity->getChapters() as $chapter) {
            if ($chapter->isEnable()) {
                $chapters[] = $this->getJsonLdChapter($chapter);
            }
        }

        $creativeWorkSeries->hasPart($chapters);

        return $creativeWorkSeries;
    }

    #[Override]
    public function getShortCodes(): array
    {
        return [StoryUrlShortcode::class];
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
        $page = $this->getEntityBySlugStory($slug);

        return $page instanceof Story;
    }

    #[Override]
    public function placeholder(): string
    {
        $placeholder = $this->globalPlaceholder('story');
        if ('' !== $placeholder) {
            return $placeholder;
        }

        return $this->configPlaceholder();
    }

    #[Override]
    public function supportsAsset(object $entity): bool
    {
        return $entity instanceof Story;
    }

    #[Override]
    public function supportsData(object $entity): bool
    {
        return $entity instanceof Story;
    }

    #[Override]
    public function supportsJsonLd(object $entity): bool
    {
        return $entity instanceof Story;
    }

    #[Override]
    public function supportsShortcode(string $className): bool
    {
        return Story::class === $className;
    }

    protected function getEntityBySlugStory(?string $slug): ?object
    {
        if (0 === substr_count((string) $slug, '/')) {
            return null;
        }

        $slugSecond = basename((string) $slug);
        $slugFirst  = dirname((string) $slug);

        $page = $this->entityManager->getRepository(Page::class)->findOneBy([
                'slug' => $slugFirst,
            ]);
        if (!$page instanceof Page) {
            return null;
        }

        if ($page->getType() != PageEnum::STORIES->value) {
            return null;
        }

        return $this->entityManager->getRepository(Story::class)->findOneBy([
                'slug' => $slugSecond,
            ]);
    }

    protected function getJsonLdChapter(Chapter $chapter): object
    {
        $schema = Schema::chapter();
        $schema->name($chapter->getTitle());

        $params = $this->slugService->forEntity($chapter);
        $schema->url($this->router->generate('front', $params, RouterInterface::ABSOLUTE_URL));
        $schema->position($chapter->getPosition());

        return $schema;
    }
}
