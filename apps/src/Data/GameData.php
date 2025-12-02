<?php

namespace Labstag\Data;

use Labstag\Entity\Game;

class GameData extends HomeData implements DataInterface
{
    #[\Override]
    public function generateSlug(object $entity): string
    {
        return parent::generateSlug($entity) . $entity->getSlug();
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
    public function supportsData(object $entity): bool
    {
        return $entity instanceof Game;
    }

    protected function getEntityBySlugGame(?string $slug): ?object
    {
        return $this->entityManager->getRepository(Game::class)->findOneBy(
            ['slug' => $slug]
        );
    }
}
