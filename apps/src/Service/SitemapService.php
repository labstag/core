<?php

namespace Labstag\Service;

use Doctrine\Persistence\ManagerRegistry;
use Labstag\Entity\Chapter;
use Labstag\Entity\Page;
use Labstag\Entity\Post;
use Labstag\Entity\Story;
use Labstag\Repository\ChapterRepository;

class SitemapService
{

    protected array $parent = [];

    public function __construct(
        protected ManagerRegistry $managerRegistry,
        protected SiteService $siteService
    )
    {
    }

    public function getData()
    {
        $tabs = array_merge(
            $this->getDataPages(),
            $this->getDataPosts(),
            $this->getDataHistories()
        );
        ksort($tabs);

        return $this->setTabsByParent($tabs, '/');
    }

    public function setTabsByParent($urls, $parent)
    {
        $tabs = [];
        foreach ($urls as $url => $data) {
            $result = str_replace((string) $parent, '', (string) $url);
            if (str_starts_with((string) $url, (string) $parent) && !isset($this->parent[$url]) && $this->verifFirstChar($result)) {
                $this->parent[$url] = true;
                $data['parent']     = $this->setTabsByParent($urls, $url);
                $tabs[$url]         = $data;
            }
        }

        return $tabs;
    }

    protected function getRepository(string $entity)
    {
        return $this->managerRegistry->getRepository($entity);
    }

    protected function verifFirstChar(string $url)
    {
        $result = substr($url, 0, 1);

        return '-' !== $result;
    }

    private function formatData($entity)
    {
        $url = $this->siteService->getSlugByEntity($entity);

        return [
            '/'.$url => ['entity' => $entity],
        ];
    }

    private function getDataChaptersByStory($story)
    {
        if (!$story instanceof Story) {
            return [];
        }

        /** @var ChapterRepository $repository */
        $repository = $this->getRepository(Chapter::class);
        $data       = $repository->getAllActivateByStory($story);

        return $this->setTabs($data);
    }

    private function getDataFromRepository(string $entityClass)
    {
        $repository = $this->getRepository($entityClass);
        $data       = $repository->getAllActivate();

        return $this->setTabs($data);
    }

    private function getDataHistories()
    {
        return $this->getDataFromRepository(Story::class);
    }

    private function getDataPages()
    {
        return $this->getDataFromRepository(Page::class);
    }

    private function getDataPosts()
    {
        return $this->getDataFromRepository(Post::class);
    }

    private function setTabs($data)
    {
        $tabs = [];
        foreach ($data as $row) {
            $tabs = array_merge(
                $tabs,
                $this->formatData($row),
                $this->getDataChaptersByStory($row)
            );
        }

        return $tabs;
    }
}
