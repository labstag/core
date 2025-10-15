<?php

namespace Labstag\Service;

use DateTime;
use Exception;
use Labstag\Entity\Season;
use Labstag\Entity\Serie;
use Labstag\Repository\SeasonRepository;
use Symfony\Component\HttpFoundation\File\UploadedFile;

final class SeasonService
{
    public function __construct(
        private SeasonRepository $seasonRepository,
    )
    {
    }

    public function updateSerie(Serie $serie, array $details): bool
    {
        $seasons = $details['tmdb']['seasons'] ?? [];

        if (0 === count($seasons)) {
            foreach ($serie->getSeasons() as $season) {
                $this->seasonRepository->remove($season);
            }

            return true;
        }

        $counter = 0;
        foreach ($seasons as $data) {
            if (0 == $data['season_number']) {
                continue;
            }

            $season = $this->setSeason($serie, $data);

            ++$counter;

            $this->seasonRepository->persist($season);
            $this->seasonRepository->flush($counter);
        }

        $this->seasonRepository->flush();

        return true;
    }

    private function getImgImdb(string $img): string
    {
        return 'https://image.tmdb.org/t/p/w300_and_h450_bestv2' . $img;
    }

    /**
     * @param array<string, mixed> $data
     */
    private function getImgSerie(array $data): string
    {
        if (isset($data['poster_path'])) {
            return $this->getImgImdb($data['poster_path']);
        }

        return '';
    }

    private function setSeason(Serie $serie, array $data): Season
    {
        $season = $this->seasonRepository->findOneBy(
            [
                'refserie' => $serie,
                'number'   => $data['season_number'],
            ]
        );
        if (!$season instanceof Season) {
            $season = new Season();
            $season->setRefserie($serie);
            $season->setNumber($data['season_number']);
        }

        $season->setAirDate(new DateTime($data['air_date']));
        $season->setTmdb($data['id']);
        if (isset($data['overview']) && '' != $data['overview']) {
            $season->setOverview($data['overview']);
        }

        $this->updateImageSerie($season, $data);

        return $season;
    }

    /**
     * @param array<string, mixed> $details
     */
    private function updateImageSerie(Season $season, array $details): bool
    {
        $poster = $this->getImgSerie($details);
        if ('' === $poster) {
            return false;
        }

        try {
            $tempPath = tempnam(sys_get_temp_dir(), 'poster_');

            // Télécharger l'image et l'écrire dans le fichier temporaire
            file_put_contents($tempPath, file_get_contents($poster));

            $uploadedFile = new UploadedFile(
                path: $tempPath,
                originalName: basename($tempPath),
                mimeType: mime_content_type($tempPath),
                test: true
            );

            $season->setImgFile($uploadedFile);

            return true;
        } catch (Exception) {
            return false;
        }
    }
}
