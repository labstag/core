<?php

namespace Labstag\Data;

use Labstag\Entity\Game;
use Labstag\Entity\Page;
use Labstag\Enum\PageEnum;

class GameData extends PageData implements DataInterface
{
    #[\Override]
    public function generateSlug(object $entity): array
    {
        $entityRepository = $this->entityManager->getRepository(Page::class);
        $page             = $entityRepository->findOneBy(
            [
                'type' => PageEnum::GAMES->value,
            ]
        );

        $slug = parent::generateSlugPage($page);
        $slug['slug'] .= '/' . $entity->getSlug();

        return $slug;
    }

    #[\Override]
    public function getEntity(?string $slug): object
    {
        return $this->getEntityBySlugGame($slug);
    }

    #[\Override]
    public function getShortCodes(): array
    {
        return [];
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
        $game = $this->getEntityBySlugGame($slug);

        return $game instanceof Game;
    }

    #[\Override]
    public function placeholder(): string
    {
        $placeholder = $this->globalPlaceholder('game');
        if ('' !== $placeholder) {
            return $placeholder;
        }

        return $this->configPlaceholder();
    }

    #[\Override]
    public function supportsAsset(object $entity): bool
    {
        return $entity instanceof Game;
    }

    #[\Override]
    public function supportsData(object $entity): bool
    {
        return $entity instanceof Game;
    }

    protected function getEntityBySlugGame(?string $slug): ?object
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

        if ($page->getType() != PageEnum::GAMES->value) {
            return null;
        }

        return $this->entityManager->getRepository(Game::class)->findOneBy(
            ['slug' => $slugSecond]
        );
    }
}
