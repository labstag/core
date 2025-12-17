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
use Labstag\Entity\User;
use Labstag\Entity\VideoParagraph;
use Labstag\Enum\PageEnum;
use Labstag\Service\ConfigurationService;
use Labstag\Service\FileService;
use Labstag\Service\ParagraphService;
use Labstag\Service\VideoService;
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
        protected VideoService $videoService,
        protected ConfigurationService $configurationService,
    )
    {
    }

    public function execute(): void
    {
        $title = $this->pageCinemaTitleTemplate->getTemplate()->getText();

        $entityRepository = $this->entityManager->getRepository(Page::class);
        $page             = $entityRepository->findOneBy(
            ['title' => $title]
        );

        $configuration = $this->configurationService->getConfiguration();
        if (!$page instanceof Page) {
            $page = new Page();
            $page->setRefuser($this->getUser());
            $page->setType(PageEnum::PAGE->value);
            $page->setEnable(true);
            $page->setTitle($title);
            $home = $entityRepository->findOneBy(
                [
                    'type' => PageEnum::HOME->value,
                ]
            );
            $page->setPage($home);
            $entityRepository->save($page);
        }

        $page->setResume($this->pageCinemaResumeTemplate->getTemplate()->getHtml());
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

        $data = $this->theMovieDbApi->movies()->discovers(
            filters: [
                'with_release_type' => '2|3',
                'region'            => $region,
                'release_date.gte'  => $gte->format('Y-m-d'),
                'release_date.lte'  => $lte->format('Y-m-d'),
            ],
            language: $locale
        );
        usort(
            $data['results'],
            fn (array $result1, array $result2): int => strcasecmp($result1['title'] ?? '', $result2['title'] ?? '')
        );

        return $data;
    }

    private function getUser(): ?object
    {
        $configuration = $this->configurationService->getConfiguration();

        $userId = $configuration->getDefaultUser();

        return $this->entityManager->getRepository(User::class)->find($userId);
    }

    private function setMovie(Page $page, array $movieData, string $locale, array &$images, int $key): void
    {
        if (!isset($movieData['release_date'])) {
            return;
        }

        $movieTitle  = $movieData['title'] ?? 'Titre inconnu';
        $cast        = $this->theMovieDbApi->movies()->getCredits($movieData['id'], $locale);
        $releaseDate = new DateTime($movieData['release_date']);
        $poster      = $this->theMovieDbApi->images()->getPosterUrl($movieData['poster_path'] ?? '');
        if (is_null($poster)) {
            return;
        }

        $paragraph = $this->paragraphService->addParagraph($page, 'text-img');
        if (!$paragraph instanceof TextImgParagraph) {
            return;
        }

        $paragraph->setLeftposition(($key % 2) === 0);
        $html = $this->pageMovieInfoTemplate->getTemplate()->getHtml();
        $casts = $cast['cast'] ?? [];
        $html  = str_replace(
            [
                '%title%',
                '%release_date%',
                '%overview%',
                '%cast%',
                '%url%',
            ],
            [
                $movieTitle,
                $releaseDate->format('d/m/Y'),
                (string) $movieData['overview'],
                implode(', ', array_map(fn (array $actor) => $actor['name'], array_slice($casts, 0, 5))),
                'https://www.themoviedb.org/movie/' . $movieData['id'],
            ],
            $html
        );
        $paragraph->setContent($html);
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

        $patchwork = $this->fileService->setImgPatchwork($images);
        if (!is_null($patchwork)) {
            $this->fileService->setUploadedFile($patchwork, $page, 'imgFile');
        }
    }

    private function setVideo(Page $page, array $movieData): void
    {
        $videos   = $this->theMovieDbApi->getVideosMovie($movieData['id']);
        $backdrop = $this->theMovieDbApi->images()->getBackdropUrl($movieData['backdrop_path'] ?? '');
        $trailer = $this->videoService->getTrailer($videos);
        if (is_null($trailer)) {
            return;
        }

        $paragraph = $this->paragraphService->addParagraph($page, 'video');
        if (is_null($paragraph) || !$paragraph instanceof VideoParagraph) {
            return;
        }

        $paragraph->setUrl($trailer);
        if (!is_null($backdrop)) {
            $this->fileService->setUploadedFile($backdrop, $paragraph, 'imgFile');
        }
    }
}
