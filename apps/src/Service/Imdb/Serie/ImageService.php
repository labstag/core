<?php

declare(strict_types=1);

namespace Labstag\Service\Imdb\Serie;

use Labstag\Api\TheMovieDbApi;
use Labstag\Entity\Serie;
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

    public function updateImageBackdrop(Serie $serie, array $details): bool
    {
        $entityRepository = $this->entityManager->getRepository(Serie::class);
        $backdrop = $this->theMovieDbApi->images()->getBackdropUrl($details['tmdb']['backdrop_path'] ?? '');
        if (is_null($backdrop)) {
            $serie->setBackdropFile();
            $serie->setBackdrop(null);
            $entityRepository->save($serie);

            return false;
        }

        $this->fileUploadService->setUploadedFile($backdrop, $serie, 'backdropFile');
        $entityRepository->save($serie);

        return true;
    }

    public function updateImagePoster(Serie $serie, array $details): bool
    {
        $entityRepository = $this->entityManager->getRepository(Serie::class);
        $poster = $this->theMovieDbApi->images()->getPosterUrl($details['tmdb']['poster_path'] ?? '');
        if (is_null($poster)) {
            $serie->setPosterFile();
            $serie->setPoster(null);
            $entityRepository->save($serie);

            return false;
        }

        $this->fileUploadService->setUploadedFile($poster, $serie, 'posterFile');
        $entityRepository->save($serie);

        return true;
    }
}
