<?php

declare(strict_types=1);

namespace Labstag\Service\Imdb\Serie;

use Labstag\Entity\Serie;
use Labstag\Entity\SerieCategory;
use Labstag\Service\CategoryService as BaseCategoryService;

final class CategoryService
{
    public function __construct(
        private BaseCategoryService $baseCategoryService,
    )
    {
    }

    public function updateCategory(Serie $serie, array $details): bool
    {
        if (!isset($details['tmdb']['genres']) || 0 === count($details['tmdb']['genres'])) {
            return false;
        }

        foreach ($serie->getCategories() as $category) {
            $serie->removeCategory($category);
        }

        foreach ($details['tmdb']['genres'] as $genre) {
            $title    = trim((string) $genre['name']);
            $category = $this->baseCategoryService->getType($title, SerieCategory::class);

            $serie->addCategory($category);
        }

        return true;
    }
}
