<?php

declare(strict_types=1);

namespace Labstag\Service\Imdb\Movie;

use Labstag\Api\TheMovieDbApi;
use Labstag\Entity\Movie;
use Labstag\Service\FileService;

final class ImageService
{
    public function __construct(
        private FileService $fileService,
        private TheMovieDbApi $theMovieDbApi,
    )
    {
    }

    public function updateImageBackdrop(Movie $movie, array $details): bool
    {
        $backdrop = $this->theMovieDbApi->images()->getBackdropUrl($details['tmdb']['backdrop_path'] ?? '');
        if (is_null($backdrop)) {
            $movie->setBackdropFile();
            $movie->setBackdrop(null);

            return false;
        }

        $this->fileService->setUploadedFile($backdrop, $movie, 'backdropFile');

        return true;
    }

    public function updateImagePoster(Movie $movie, array $details): bool
    {
        $poster = $this->theMovieDbApi->images()->getPosterUrl($details['tmdb']['poster_path'] ?? '');
        if (is_null($poster)) {
            $movie->setPosterFile();
            $movie->setPoster(null);

            return false;
        }

        $this->fileService->setUploadedFile($poster, $movie, 'posterFile');

        return true;
    }
}
