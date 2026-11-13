<?php

declare(strict_types=1);

namespace Labstag\Service\Igdb\Game;

use Labstag\Api\LibreTranslationApi;
use Labstag\Entity\Game;
use Labstag\Entity\GameCategory;
use Labstag\Service\CategoryService;
use Labstag\Service\FileService;

final class GameUpdateService
{
    public function __construct(
        private FileService $fileService,
        private CategoryService $categoryService,
        private LibreTranslationApi $libreTranslationApi,
    )
    {
    }

    public function updateArtworks(Game $game, array $data): bool
    {
        if (!isset($data['artworks']) || !is_array($data['artworks'])) {
            $game->setArtworks([]);

            return true;
        }

        $artworks = [];
        foreach ($data['artworks'] as $result) {
            if (is_null($result)) {
                continue;
            }

            $artworks[] = $this->igdbApi->buildImageUrl($result['image_id'], 'original');
        }

        $game->setArtworks($artworks);

        return true;
    }

    public function updateFranchises(Game $game, array $data): bool
    {
        if (!isset($data['franchises']) || !is_array($data['franchises'])) {
            foreach ($game->getFranchises() as $franchise) {
                $game->removeFranchise($franchise);
            }

            return true;
        }

        foreach ($data['franchises'] as $franchiseData) {
            $franchise = $this->getFranchise($franchiseData);
            if (is_null($franchise)) {
                continue;
            }

            $game->addFranchise($franchise);
        }

        return true;
    }

    public function updateGame(Game $game, array $data): bool
    {
        $summary = $data['summary'] ?? '';
        $summary = explode("\n", $summary);

        $new     = [];
        foreach ($summary as $text) {
            if ('' !== trim($text)) {
                $translation = $this->libreTranslationApi->translate($text, 'en', 'fr');
                $new[]       = trim($translation['translatedText']);
            }
        }

        $summary = '<p>' . implode('</p><p>', $new) . '</p>';
        $game->setSummary($summary);

        return true;
    }

    public function updateGenres(Game $game, array $data): bool
    {
        if (!isset($data['genres']) || !is_array($data['genres'])) {
            foreach ($game->getCategories() as $genre) {
                $game->removeCategory($genre);
            }

            return true;
        }

        foreach ($data['genres'] as $result) {
            $category = $this->categoryService->getType($result['name'], GameCategory::class);

            $game->addCategory($category);
        }

        return true;
    }

    public function updateImage(Game $game, array $data): bool
    {
        if (!isset($data['cover']['image_id']) || empty($data['cover']['image_id'])) {
            return false;
        }

        $imageUrl = $this->igdbApi->buildImageUrl($data['cover']['image_id'], 'original');
        $this->fileService->setUploadedFile($imageUrl, $game, 'imgFile');

        return true;
    }

    public function updateScreenshots(Game $game, array $data): bool
    {
        if (!isset($data['screenshots']) || !is_array($data['screenshots'])) {
            $game->setScreenshots([]);

            return true;
        }

        $screenshots = [];
        foreach ($data['screenshots'] as $result) {
            if (is_null($result)) {
                continue;
            }

            $screenshots[] = $this->igdbApi->buildImageUrl($result['image_id'], 'original');
        }

        $game->setScreenshots($screenshots);

        return true;
    }

    public function updateVideos(Game $game, array $data): bool
    {
        if (!isset($data['videos']) || !is_array($data['videos'])) {
            $game->setVideos([]);

            return true;
        }

        $videos  = [];
        foreach ($data['videos'] as $result) {
            if (is_null($result)) {
                continue;
            }

            $videos[] = $result['video_id'];
        }

        $game->setVideos($videos);

        return true;
    }

}
