<?php

namespace Labstag\Service;

use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Labstag\Entity\Chapter;
use Labstag\Entity\Page;
use Labstag\Entity\Post;
use Labstag\Entity\Story;
use Labstag\Lib\ServiceEntityRepositoryLib;
use Labstag\Repository\ChapterRepository;

class SitemapService
{

    /**
     * @var string[]
     */
    protected array $parent = [];

    public function __construct(
        protected EntityManagerInterface $entityManager,
        protected SiteService $siteService,
    )
    {
    }

    /**
     * @return mixed[]
     */
    public function getData(bool $all = false): array
    {
        $configuration = $this->siteService->getConfiguration();

        $tabs = $this->getDataPages();
        if ($configuration->isSitemapPosts() || $all == true) {
            $tabs = array_merge($tabs, $this->getDataPosts());
        }

        if ($configuration->isSitemapStory() || $all == true) {
            $tabs = array_merge($tabs, $this->getDataStory());
        }

        ksort($tabs);
        $this->parent = [];

        return $this->setTabsByParent($tabs, '/');
    }

    /**
     * @param mixed[] $urls
     *
     * @return mixed[]
     */
    public function setTabsByParent(array $urls, string $parent): array
    {
        $tabs = [];
        foreach ($urls as $url => $data) {
            $result = str_replace($parent, '', (string) $url);
            if (str_starts_with((string) $url, $parent) && !isset($this->parent[$url]) && $this->verifFirstChar(
                $result
            )
            ) {
                $this->parent[$url] = true;
                $data['parent'] = $this->setTabsByParent($urls, $url);
                $tabs[$url] = $data;
            }
        }

        return $tabs;
    }

    protected function getRepository(string $entity): ServiceEntityRepositoryLib
    {
        $entityRepository = $this->entityManager->getRepository($entity);
        if (!$entityRepository instanceof ServiceEntityRepositoryLib) {
            throw new Exception('Repository not found');
        }

        return $entityRepository;
    }

    protected function verifFirstChar(string $url): bool
    {
        $result = substr($url, 0, 1);

        return $result !== '-';
    }

    /**
     * @return mixed[]
     */
    private function formatData(object $entity): array
    {
        $url = $this->siteService->getSlugByEntity($entity);

        return [
            '/' . $url => ['entity' => $entity],
        ];
    }

    /**
     * @return mixed[]
     */
    private function getDataChaptersByStory(object $story): array
    {
        if (!$story instanceof Story) {
            return [];
        }

        /** @var ChapterRepository $serviceEntityRepositoryLib */
        $serviceEntityRepositoryLib = $this->getRepository(Chapter::class);
        $data = $serviceEntityRepositoryLib->getAllActivateByStory($story);

        return $this->setTabs($data);
    }

    /**
     * @return mixed[]
     */
    private function getDataFromRepository(string $entityClass): array
    {
        $serviceEntityRepositoryLib = $this->getRepository($entityClass);
        if (!method_exists($serviceEntityRepositoryLib, 'getAllActivate')) {
            return [];
        }

        $data = $serviceEntityRepositoryLib->getAllActivate();

        return $this->setTabs($data);
    }

    /**
     * @return mixed[]
     */
    private function getDataPages(): array
    {
        return $this->getDataFromRepository(Page::class);
    }

    /**
     * @return mixed[]
     */
    private function getDataPosts(): array
    {
        return $this->getDataFromRepository(Post::class);
    }

    /**
     * @return mixed[]
     */
    private function getDataStory(): array
    {
        return $this->getDataFromRepository(Story::class);
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
            $tabs = array_merge($tabs, $this->formatData($row), $this->getDataChaptersByStory($row));
        }

        return $tabs;
    }
}
