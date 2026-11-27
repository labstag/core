<?php

declare(strict_types=1);

namespace Labstag\Service\Imdb\Movie;

use Doctrine\ORM\EntityManagerInterface;
use Labstag\Api\TheMovieDbApi;
use Labstag\Entity\Movie;
use Labstag\Service\File\FileUploadService;

class ImageService
{
    public function __construct(
        private FileUploadService $fileUploadService,
        private EntityManagerInterface $entityManager,
        private TheMovieDbApi $theMovieDbApi,
    )
    {
    }

    public function updateImageBackdrop(Movie $movie, array $details): bool
    {
        $backdrop = $this->theMovieDbApi->images()->getBackdropUrl($details['tmdb']['backdrop_path'] ?? '');
        if (is_null($backdrop)) {
            return false;
        }

        $this->fileUploadService->setUploadedFile($backdrop, $movie, 'backdropFile');
        $entityRepository = $this->entityManager->getRepository(Movie::class);
        $entityRepository->save($movie);

        return true;
    }

    public function updateImagePoster(Movie $movie, array $details): bool
    {
        $poster = $this->theMovieDbApi->images()->getPosterUrl($details['tmdb']['poster_path'] ?? '');
        if (is_null($poster)) {
            return false;
        }

        $this->fileUploadService->setUploadedFile($poster, $movie, 'posterFile');
        $entityRepository = $this->entityManager->getRepository(Movie::class);
        $entityRepository->save($movie);

        return true;
    }
}
