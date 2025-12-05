<?php

namespace Labstag\Generate;

use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Labstag\Api\TheMovieDbApi;
use Labstag\Entity\Configuration;
use Labstag\Entity\HeadParagraph;
use Labstag\Entity\Page;
use Labstag\Entity\TextImgParagraph;
use Labstag\Entity\TextParagraph;
use Labstag\Entity\VideoParagraph;
use Labstag\Service\ConfigurationService;
use Labstag\Service\FileService;
use Labstag\Service\ParagraphService;
use Labstag\Template\PageCinemaInfoTemplate;
use Labstag\Template\PageCinemaResumeTemplate;
use Labstag\Template\PageCinemaTitleTemplate;
use Labstag\Template\PageMovieInfoTemplate;

class CinemaGenerate
{
    public function __construct(
        protected PageCinemaResumeTemplate $pageCinemaResumeTemplate,
        protected FileService $fileService,
        protected PageCinemaTitleTemplate $pageCinemaTitleTemplate,
        protected PageMovieInfoTemplate $pageMovieInfoTemplate,
        protected PageCinemaInfoTemplate $pageCinemaInfoTemplate,
        protected EntityManagerInterface $entityManager,
        protected ParagraphService $paragraphService,
        protected TheMovieDbApi $theMovieDbApi,
        protected ConfigurationService $configurationService,
    )
    {
    }

    public function execute(): void
    {
        $title = $this->pageCinemaTitleTemplate->getTemplate();

        $entityRepository = $this->entityManager->getRepository(Page::class);
        $page           = $entityRepository->findOneBy(
            [
                'title' => $title->getText(),
            ]
        );

        if (!$page instanceof Page) {
            $page = new Page();
            $page->setEnable(true);
            $page->setTitle($title->getText());
            $entityRepository->save($page);
        }

        $configuration = $this->configurationService->getConfiguration();
        $page->setRefuser($configuration->getDefaultUser());
        $this->setParagraphs($page, $configuration);
        $entityRepository->save($page);
    }

    public function setParagraphs(Page $page, Configuration $configuration): void
    {
        $gte    = new DateTime();
        $gte->modify('monday this week');

        $lte = new DateTime();
        $lte->modify('sunday this week');

        $paragraphs = $page->getParagraphs();
        foreach ($paragraphs as $paragraph) {
            if (!$paragraph instanceof HeadParagraph) {
                $page->removeParagraph($paragraph);
            }
        }

        $paragraph = $this->paragraphService->addParagraph($page, 'text');
        if (is_null($paragraph) || !$paragraph instanceof TextParagraph) {
            return;
        }

        $cinemaInfo = $this->pageCinemaInfoTemplate->getTemplate();
        $html       = $cinemaInfo->getHtml();
        $html       = str_replace(['%min%', '%max%'], [$gte->format('d/m/Y'), $lte->format('d/m/Y')], $html);

        $paragraph->setContent($html);

        $movies = $this->getMovies($gte, $lte, $configuration);
        $this->setMovies($page, $movies, $configuration->getLanguageTmdb());
    }

    private function getMovies(DateTime $gte, DateTime $lte, Configuration $configuration): array
    {
        $locale        = $configuration->getLanguageTmdb();

        $region = $configuration->getRegionTmdb() ?? 'FR';

        return $this->theMovieDbApi->movies()->discovers(
            filters: [
                'with_release_type' => '2|3',
                'region'            => $region,
                'release_date.gte'  => $gte->format('Y-m-d'),
                'release_date.lte'  => $lte->format('Y-m-d'),
            ],
            language: $locale
        );
    }

    private function getTrailer(array $videos): ?string
    {
        foreach ($videos['results'] as $result) {
            if ('YouTube' == $result['site'] && 'Trailer' == $result['type']) {
                return 'https://www.youtube.com/watch?v=' . $result['key'];
            }
        }

        if (1 === count($videos['results']) && 'YouTube' == $videos['results'][0]['site']
        ) {
            return 'https://www.youtube.com/watch?v=' . $videos['results'][0]['key'];
        }

        return null;
    }

    private function setMovie(Page $page, array $movieData, string $locale, array &$images, int $key): void
    {
        if (!isset($movieData['release_date'])) {
            return;
        }

        $movieTitle = $movieData['title'] ?? 'Titre inconnu';
        $cast       = $this->theMovieDbApi->movies()->getCredits($movieData['id'], $locale);
        $overview    = $movieData['overview'] ?? 'Pas de description disponible.';
        $releaseDate = new DateTime($movieData['release_date']);
        $movieLine   = sprintf(
            "\n\n<h2>%s</h2><br />(Sortie le %s)\n%s%s<br /><a href=\"https://www.themoviedb.org/movie/%d\" target=\"_blank\">Aller sur la page TMDB</a></p>",
            $movieTitle,
            $releaseDate->format('d/m/Y'),
            sprintf('<p>%s</p>', $overview),
            isset($cast['cast']) ? '<p>Avec: ' . implode(
                ', ',
                array_map(fn (array $actor) => $actor['name'], array_slice($cast['cast'], 0, 5))
            ) . '</p>' : '',
            $movieData['id']
        );

        $paragraph = $this->paragraphService->addParagraph($page, 'text-img');
        if (!$paragraph instanceof TextImgParagraph) {
            return;
        }

        $paragraph->setLeftposition(($key % 2) === 0);
        $paragraph->setContent($movieLine);

        $poster = $this->theMovieDbApi->images()->getPosterUrl($movieData['poster_path'] ?? '');
        if (is_null($poster)) {
            $paragraph->setImgFile();
            $paragraph->setImg(null);

            return;
        }

        $images[] = $poster;
        $this->fileService->setUploadedFile($poster, $paragraph, 'imgFile');

        $this->setVideo($page, $movieData);
    }

    private function setMovies(Page $page, array $movies, string $locale): void
    {
        $images = [];
        foreach ($movies['results'] as $key => $movieData) {
            $this->setMovie($page, $movieData, $locale, $images, $key);
        }

        $patwork = $this->fileService->setImgPatwork($images);
        if (!is_null($patwork)) {
            $this->fileService->setUploadedFile($patwork, $page, 'imgFile');
        }
    }

    private function setVideo(Page $page, array $movieData): void
    {
        $videos = $this->theMovieDbApi->getVideosMovie($movieData['id']);
        if (!is_array($videos)) {
            return;
        }

        $trailer = $this->getTrailer($videos);
        if (is_null($trailer)) {
            return;
        }

        $paragraph = $this->paragraphService->addParagraph($page, 'video');
        if (is_null($paragraph) || !$paragraph instanceof VideoParagraph) {
            return;
        }

        $paragraph->setUrl($trailer);
    }
}
