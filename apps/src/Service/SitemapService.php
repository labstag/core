<?php

namespace Labstag\Service;

use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Labstag\Entity\Chapter;
use Labstag\Entity\Page;
use Labstag\Entity\Post;
use Labstag\Entity\Story;
use Labstag\Enum\PageEnum;
use Labstag\Repository\ChapterRepository;
use Labstag\Repository\ServiceEntityRepositoryAbstract;

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

        ksort($tabs);
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
    private function getDataChaptersByStory(object $story): array
    {
        if (!$story instanceof Story) {
            return [];
        }

        /** @var ChapterRepository $serviceEntityRepositoryAbstract */
        $serviceEntityRepositoryAbstract = $this->getRepository(Chapter::class);
        $data                            = $serviceEntityRepositoryAbstract->getAllActivateByStory($story);

        return $this->setTabs($data);
    }

    /**
     * @return mixed[]
     */
    private function getDataFromRepository(string $entityClass): array
    {
        $serviceEntityRepositoryAbstract = $this->getRepository($entityClass);
        if (!method_exists($serviceEntityRepositoryAbstract, 'getAllActivate')) {
            return [];
        }

        $data = $serviceEntityRepositoryAbstract->getAllActivate();

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
        $listing = $this->slugService->getPageByType(PageEnum::POSTS->value);
        if (!is_object($listing) || !$listing->isEnable()) {
            return [];
        }

        return $this->getDataFromRepository(Post::class);
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

        return $this->getDataFromRepository(Story::class);
    }

    /**
     * @return ServiceEntityRepositoryAbstract<object>
     */
    private function getRepository(string $entity): ServiceEntityRepositoryAbstract
    {
        $entityRepository = $this->entityManager->getRepository($entity);
        if (!$entityRepository instanceof ServiceEntityRepositoryAbstract) {
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
            $tabs = array_merge($tabs, $this->formatData($row), $this->getDataChaptersByStory($row));
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
                $data['parent']     = $this->setTabsByParent($urls, $url);
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
