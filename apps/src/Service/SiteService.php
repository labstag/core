<?php

namespace Labstag\Service;

use Labstag\Repository\ChapterRepository;
use Labstag\Repository\EditoRepository;
use Labstag\Repository\HistoryRepository;
use Labstag\Repository\PageRepository;
use Labstag\Repository\PostRepository;
use ReflectionClass;
use Twig\Environment;

class SiteService
{
    public function __construct(
        protected ChapterRepository $chapterRepository,
        protected EditoRepository $editoRepository,
        protected HistoryRepository $historyRepository,
        protected PageRepository $pageRepository,
        protected PostRepository $postRepository,
        protected Environment $twigEnvironment
    )
    {
    }

    public function getDataByEntity(object $entity)
    {
        return [
            'meta'    => $this->getMetaByEntity($entity),
            'content' => $this->getContentByEntity($entity),
        ];
    }

    public function getEntityBySlug($slug)
    {
        if ('' == $slug) {
            return $this->pageRepository->findOneBy(
                ['home' => true]
            );
        }

        return $slug;
    }

    public function getViewByEntity(object $entity)
    {
        $reflectionClass = new ReflectionClass($entity);
        $entityName      = ucfirst($reflectionClass->getShortName());

        return $this->getViewByEntityName($entity, $entityName);
    }

    protected function getContentByEntity($entity)
    {
        return [];
    }

    protected function getMetaByEntity(object $entity)
    {
        return $entity->getMeta();
    }

    protected function getRepositories(): array
    {
        return [
            'chapter' => $this->chapterRepository,
            'edito'   => $this->editoRepository,
            'history' => $this->historyRepository,
            'page'    => $this->pageRepository,
            'post'    => $this->postRepository,
        ];
    }

    protected function getViewByEntityName(object $entity, string $entityName)
    {
        $loader = $this->twigEnvironment->getLoader();
        $files  = [
            'views/'.$entityName.'.html.twig',
            'views/default.html.twig',
        ];
        $view   = end($files);
        $loader = $this->twigEnvironment->getLoader();
        foreach ($files as $file) {
            if (!$loader->exists($file)) {
                continue;
            }

            $view = $file;

            break;
        }

        return $view;
    }
}
