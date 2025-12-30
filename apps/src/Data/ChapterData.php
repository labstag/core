<?php

namespace Labstag\Data;

use Labstag\Entity\Chapter;
use Override;
use Spatie\SchemaOrg\Schema;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class ChapterData extends StoryData implements DataInterface
{
    #[Override]
    public function asset(mixed $entity, string $field): string
    {
        $asset = $this->fileService->asset($entity, $field);
        if ('' !== $asset) {
            return $asset;
        }

        return parent::asset($entity->getStory(), $field);
    }

    public function getDefaultImage(object $entity): ?string
    {
        return $entity->getImg();
    }

    #[Override]
    public function generateSlug(object $entity): array
    {
        $slug = parent::generateSlug($entity->getRefstory());
        $slug['slug'] .= '/' . $entity->getSlug();

        return $slug;
    }

    #[Override]
    public function getEntity(?string $slug): object
    {
        return $this->getEntityBySlugChapter($slug);
    }

    #[Override]
    public function getJsonLd(object $entity): object
    {
        $schema = $this->getJsonLdChapter($entity);

        $creativeWorkSeries = Schema::creativeWorkSeries();
        $creativeWorkSeries->name($entity->getRefstory()->getTitle());

        $params = $this->slugService->forEntity($entity->getRefstory());
        $creativeWorkSeries->url($this->router->generate('front', $params, UrlGeneratorInterface::ABSOLUTE_URL));
        $schema->isPartOf($creativeWorkSeries);

        return $schema;
    }

    #[Override]
    public function getTitle(object $entity): string
    {
        return $entity->getTitle();
    }

    #[Override]
    public function getTitleMeta(object $entity): string
    {
        return parent::getTitleMeta($entity->getRefstory()) . ' - ' . $this->getTitle($entity);
    }

    #[Override]
    public function match(?string $slug): bool
    {
        $page = $this->getEntityBySlugChapter($slug);

        return $page instanceof Chapter;
    }

    #[Override]
    public function placeholder(): string
    {
        $placeholder = $this->globalPlaceholder('chapter');
        if ('' !== $placeholder) {
            return $placeholder;
        }

        return parent::placeholder();
    }

    #[Override]
    public function supportsAsset(object $entity): bool
    {
        return $entity instanceof Chapter;
    }

    #[Override]
    public function supportsData(object $entity): bool
    {
        return $entity instanceof Chapter;
    }

    #[Override]
    public function supportsJsonLd(object $entity): bool
    {
        return $entity instanceof Chapter;
    }

    protected function getEntityBySlugChapter(?string $slug): ?object
    {
        if (0 === substr_count((string) $slug, '/')) {
            return null;
        }

        $slugSecond = basename((string) $slug);
        $slugFirst  = dirname((string) $slug);

        if (false === parent::match($slugFirst)) {
            return null;
        }

        $story      = parent::getEntity($slugFirst);

        return $this->entityManager->getRepository(Chapter::class)->findOneBy(
            [
                'refstory' => $story,
                'slug'     => $slugSecond,
            ]
        );
    }
}
