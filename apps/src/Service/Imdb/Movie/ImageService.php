<?php

declare(strict_types=1);

namespace Labstag\Service\Imdb\Movie;

use Labstag\Api\TheMovieDbApi;
use Labstag\Entity\Movie;
use Labstag\Service\File\FileUploadService;
use Doctrine\ORM\EntityManagerInterface;

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
        $entityRepository = $this->entityManager->getRepository(Movie::class);
        $backdrop = $this->theMovieDbApi->images()->getBackdropUrl($details['tmdb']['backdrop_path'] ?? '');
        if (is_null($backdrop)) {
            $movie->setBackdropFile();
            $movie->setBackdrop(null);
            $entityRepository->save($movie);

            return false;
        }

        $this->fileUploadService->setUploadedFile($backdrop, $movie, 'backdropFile');
        $entityRepository->save($movie);

        return true;
    }

    public function updateImagePoster(Movie $movie, array $details): bool
    {
        $entityRepository = $this->entityManager->getRepository(Movie::class);
        $poster = $this->theMovieDbApi->images()->getPosterUrl($details['tmdb']['poster_path'] ?? '');
        if (is_null($poster)) {
            $movie->setPosterFile();
            $movie->setPoster(null);
            $entityRepository->save($movie);

            return false;
        }

        $this->fileUploadService->setUploadedFile($poster, $movie, 'posterFile');
        $entityRepository->save($movie);

        return true;
    }
}
