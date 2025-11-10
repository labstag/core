<?php

namespace Labstag\Data;

use Labstag\Entity\Page;
use Labstag\Entity\Saga;
use Labstag\Enum\PageEnum;

class SagaData extends PageData implements DataInterface
{
    #[\Override]
    public function generateSlug(object $entity): string
    {
        $page = $this->entityManager->getRepository(Page::class)->findOneBy(
            [
                'type' => PageEnum::MOVIES->value,
            ]
        );

        return parent::generateSlug($page) . '/' . $entity->getSlug();
    }

    #[\Override]
    public function getEntity(?string $slug): object
    {
        return $this->getEntityBySlugSaga($slug);
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
        $page = $this->getEntityBySlugSaga($slug);

        return $page instanceof Saga;
    }

    #[\Override]
    public function placeholder(): string
    {
        $placeholder = $this->globalPlaceholder('saga');
        if ('' !== $placeholder) {
            return $placeholder;
        }

        return $this->configPlaceholder();
    }

    #[\Override]
    public function supportsAsset(object $entity): bool
    {
        return $entity instanceof Saga;
    }

    #[\Override]
    public function supportsData(object $entity): bool
    {
        return $entity instanceof Saga;
    }

    protected function getEntityBySlugSaga(?string $slug): ?object
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

        if ($page->getType() != PageEnum::MOVIES->value) {
            return null;
        }

        return $this->entityManager->getRepository(Saga::class)->findOneBy(
            ['slug' => $slugSecond]
        );
    }
}
