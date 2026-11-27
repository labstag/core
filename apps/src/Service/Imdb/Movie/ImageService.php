<?php

declare(strict_types=1);

namespace Labstag\Service\Imdb\Movie;

use Labstag\Api\TheMovieDbApi;
use Labstag\Entity\Movie;
use Labstag\Service\File\FileUploadService;

class ImageService
{
    public function __construct(
        private FileUploadService $fileUploadService,
        private TheMovieDbApi $theMovieDbApi,
    )
    {
    }

    public function updateImageBackdrop(Movie $movie, array $details): bool
    {
        $backdrop = $this->theMovieDbApi->images()->getBackdropUrl($details['tmdb']['backdrop_path'] ?? '');
        $this->fileUploadService->setUploadedFile($backdrop, $movie, 'backdropFile');

        return true;
    }

    public function updateImagePoster(Movie $movie, array $details): bool
    {
        $poster = $this->theMovieDbApi->images()->getPosterUrl($details['tmdb']['poster_path'] ?? '');
        $this->fileUploadService->setUploadedFile($poster, $movie, 'posterFile');

        return true;
    }
}
