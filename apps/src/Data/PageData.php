<?php

namespace Labstag\Data;

use Labstag\Entity\Page;
use Labstag\Shortcode\PageUrlShortcode;

class PageData extends HomeData implements DataInterface
{
    #[\Override]
    public function generateSlug(object $entity): string
    {
        return parent::generateSlug($entity) . $entity->getSlug();
    }

    #[\Override]
    public function getEntity(?string $slug): object
    {
        return $this->getEntityBySlugPage($slug);
    }

    #[\Override]
    public function getShortCodes(): array
    {
        return [PageUrlShortcode::class];
    }

    #[\Override]
    public function getTitle(object $entity): string
    {
        return $entity->getTitle();
    }

    public function getTitleMeta(object $entity): string
    {
        return $this->getTitle($entity);
    }

    #[\Override]
    public function match(?string $slug): bool
    {
        $page = $this->getEntityBySlugPage($slug);

        return $page instanceof Page;
    }

    #[\Override]
    public function placeholder(): string
    {
        $placeholder = $this->globalPlaceholder('page');
        if ('' !== $placeholder) {
            return $placeholder;
        }

        return $this->configPlaceholder();
    }

    #[\Override]
    public function supportsAsset(object $entity): bool
    {
        return $entity instanceof Page;
    }

    #[\Override]
    public function supportsData(object $entity): bool
    {
        return $entity instanceof Page;
    }

    #[\Override]
    public function supportsShortcode(string $className): bool
    {
        return Page::class === $className;
    }

    protected function generateShortcode1(string $id): string
    {
        return sprintf('[%s:%s]', 'pageurl', $id);
    }

    protected function getEntityBySlugPage(?string $slug): ?object
    {
        return $this->entityManager->getRepository(Page::class)->findOneBy(
            ['slug' => $slug]
        );
    }
}
