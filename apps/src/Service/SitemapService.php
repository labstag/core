<?php

namespace Labstag\Service;

use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Labstag\Entity\Chapter;
use Labstag\Entity\Movie;
use Labstag\Entity\Page;
use Labstag\Entity\Post;
use Labstag\Entity\Saga;
use Labstag\Entity\Season;
use Labstag\Entity\Serie;
use Labstag\Entity\Story;
use Labstag\Enum\PageEnum;

final class SitemapService
{

    /**
     * @var string[]
     */
    private array $parent = [];

    public function __construct(
        private ConfigurationService $configurationService,
        private SlugService $slugService,
        private EntityManagerInterface $entityManager,
    )
    {
    }

    /**
     * @return mixed[]
     */
    public function getData(bool $all = false): array
    {
        $configuration = $this->configurationService->getConfiguration();

        $tabs = $this->getDataPages();
        if ($configuration->isSitemapPosts() || $all) {
            $tabs = array_merge($tabs, $this->getDataPosts());
        }

        if ($configuration->isSitemapStory() || $all) {
            $tabs = array_merge($tabs, $this->getDataStory());
        }

        if ($all) {
            $tabs = array_merge($tabs, $this->getDataSerie());
        }

        if ($all) {
            $tabs = array_merge($tabs, $this->getDataMovie());
        }

        $this->parent = [];

        return $this->setTabsByParent($tabs, '/');
    }

    /**
     * @return mixed[]
     */
    private function formatData(object $entity): array
    {
        $url = $this->slugService->forEntity($entity);

        return [
            '/' . $url => ['entity' => $entity],
        ];
    }

    /**
     * @return mixed[]
     */
    private function getDataFromRepository(string $entityClass): array
    {
        $entityRepository = $this->getRepository($entityClass);
        if (!method_exists($entityRepository, 'getAllActivate')) {
            return [];
        }

        return $entityRepository->getAllActivate();
    }

    /**
     * @return mixed[]
     */
    private function getDataPages(): array
    {
        $pages = $this->getDataFromRepository(Page::class);

        return $this->setTabs($pages);
    }

    /**
     * @return mixed[]
     */
    private function getDataPosts(): array
    {
        $listing = $this->slugService->getPageByType(PageEnum::POSTS->value);
        if (!is_object($listing) || !$listing->isEnable()) {
            return [];
        }

        $posts = $this->getDataFromRepository(Post::class);

        return $this->setTabs($posts);
    }

    /**
     * @return mixed[]
     */
    private function getDataSerie(): array
    {
        $listing = $this->slugService->getPageByType(PageEnum::SERIES->value);
        if (!is_object($listing) || !$listing->isEnable()) {
            return [];
        }

        $series           = $this->getDataFromRepository(Serie::class);
        $entityRepository = $this->entityManager->getRepository(Season::class);
        $seasons          = [];
        foreach ($series as &$serie) {
            $seasonsSerie = $entityRepository->getAllActivateBySerie($serie);
            if (0 === count($seasonsSerie)) {
                unset($serie);
            }

            $seasons = array_merge($seasons, $seasonsSerie);
        }

        return array_merge($this->setTabs($series), $this->setTabs($seasons));
    }

    /**
     * @return mixed[]
     */
    private function getDataMovie(): array
    {
        $listing = $this->slugService->getPageByType(PageEnum::MOVIES->value);
        if (!is_object($listing) || !$listing->isEnable()) {
            return [];
        }

        $movies           = $this->getDataFromRepository(Movie::class);
        $sagas = $this->getDataFromRepository(Saga::class);

        return array_merge($this->setTabs($movies), $this->setTabs($sagas));
    }

    /**
     * @return mixed[]
     */
    private function getDataStory(): array
    {
        $listing = $this->slugService->getPageByType(PageEnum::STORIES->value);
        if (!is_object($listing) || !$listing->isEnable()) {
            return [];
        }

        $stories          = $this->getDataFromRepository(Story::class);
        $entityRepository = $this->entityManager->getRepository(Chapter::class);
        $chapters         = [];
        foreach ($stories as &$story) {
            $chaptersStory = $entityRepository->getAllActivateByStory($story);
            if (0 === count($chaptersStory)) {
                unset($story);
            }

            $chapters = array_merge($chapters, $chaptersStory);
        }

        return array_merge($this->setTabs($stories), $this->setTabs($chapters));
    }

    /**
     * @return \Doctrine\ORM\EntityRepository<object>
     */
    private function getRepository(string $entity): \Doctrine\ORM\EntityRepository
    {
        $entityRepository = $this->entityManager->getRepository($entity);
        if (is_null($entityRepository)) {
            throw new Exception('Repository not found');
        }

        return $entityRepository;
    }

    /**
     * @param mixed[] $data
     *
     * @return mixed[]
     */
    private function setTabs(array $data): array
    {
        $tabs = [];
        foreach ($data as $row) {
            $tabs = array_merge($tabs, $this->formatData($row));
        }

        return $tabs;
    }

    /**
     * @param mixed[] $urls
     *
     * @return mixed[]
     */
    private function setTabsByParent(array $urls, string $parent): array
    {
        $tabs = [];
        foreach ($urls as $url => $data) {
            $result = str_replace($parent, '', (string) $url);
            if (str_starts_with((string) $url, $parent) && !isset($this->parent[$url]) && $this->verifFirstChar(
                $result
            )
            ) {
                $this->parent[$url] = true;
                $data['parent']     = $this->setTabsByParent($urls, $url . '/');
                $tabs[$url]         = $data;
            }
        }

        return $tabs;
    }

    private function verifFirstChar(string $url): bool
    {
        $result = substr($url, 0, 1);

        return '-' !== $result;
    }
}
