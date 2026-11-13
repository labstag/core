<?php

declare(strict_types=1);

namespace Labstag\Service\Imdb\Movie;

use Labstag\Entity\Movie;
use Labstag\Entity\MovieCategory;
use Labstag\Service\CategoryService as BaseCategoryService;

final class CategoryService
{
    public function __construct(
        private BaseCategoryService $baseCategoryService,
    )
    {
    }

    public function updateCategory(Movie $movie, array $details): bool
    {
        if (!isset($details['tmdb']['genres']) || 0 === count($details['tmdb']['genres'])) {
            return false;
        }

        foreach ($movie->getCategories() as $category) {
            $movie->removeCategory($category);
        }

        foreach ($details['tmdb']['genres'] as $genre) {
            $title    = trim((string) $genre['name']);
            $category = $this->baseCategoryService->getType($title, MovieCategory::class);

            $movie->addCategory($category);
        }

        return true;
    }
}
