<?php

declare(strict_types=1);

namespace Labstag\Service\Imdb\Serie;

use Labstag\Api\TheMovieDbApi;
use Labstag\Entity\Serie;
use Labstag\Service\File\FileUploadService;

class ImageService
{
    public function __construct(
        private FileUploadService $fileUploadService,
        private TheMovieDbApi $theMovieDbApi,
    )
    {
    }

    public function updateImageBackdrop(Serie $serie, array $details): bool
    {
        $backdrop = $this->theMovieDbApi->images()->getBackdropUrl($details['tmdb']['backdrop_path'] ?? '');
        $this->fileUploadService->setUploadedFile($backdrop, $serie, 'backdropFile');

        return true;
    }

    public function updateImagePoster(Serie $serie, array $details): bool
    {
        $poster = $this->theMovieDbApi->images()->getPosterUrl($details['tmdb']['poster_path'] ?? '');
        $this->fileUploadService->setUploadedFile($poster, $serie, 'posterFile');

        return true;
    }
}
