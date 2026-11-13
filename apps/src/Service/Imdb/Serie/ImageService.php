<?php

declare(strict_types=1);

namespace Labstag\Service\Imdb\Serie;

use Labstag\Api\TheMovieDbApi;
use Labstag\Entity\Serie;
use Labstag\Service\FileService;

final class ImageService
{
    public function __construct(
        private FileService $fileService,
        private TheMovieDbApi $theMovieDbApi,
    )
    {
    }

    public function updateImageBackdrop(Serie $serie, array $details): bool
    {
        $backdrop = $this->theMovieDbApi->images()->getBackdropUrl($details['tmdb']['backdrop_path'] ?? '');
        if (is_null($backdrop)) {
            $serie->setBackdropFile();
            $serie->setBackdrop(null);

            return false;
        }

        $this->fileService->setUploadedFile($backdrop, $serie, 'backdropFile');

        return true;
    }

    public function updateImagePoster(Serie $serie, array $details): bool
    {
        $poster = $this->theMovieDbApi->images()->getPosterUrl($details['tmdb']['poster_path'] ?? '');
        if (is_null($poster)) {
            $serie->setPosterFile();
            $serie->setPoster(null);

            return false;
        }

        $this->fileService->setUploadedFile($poster, $serie, 'posterFile');

        return true;
    }
}
