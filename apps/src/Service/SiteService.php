<?php

namespace Labstag\Service;

use Labstag\Entity\Meta;
use Labstag\Repository\ChapterRepository;
use Labstag\Repository\HistoryRepository;
use Labstag\Repository\PageRepository;
use Labstag\Repository\PostRepository;
use ReflectionClass;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Twig\Environment;

class SiteService
{

    public $editoRepository;

    public function __construct(
        protected ChapterRepository $chapterRepository,
        protected TokenStorageInterface $tokenStorage,
        protected RequestStack $requestStack,
        protected HistoryRepository $historyRepository,
        protected PageRepository $pageRepository,
        protected PostRepository $postRepository,
        protected Environment $twigEnvironment,
    )
    {
    }

    public function getDataByEntity(object $entity)
    {
        return [
            'meta'       => $this->getMetaByEntity($entity->getMeta()),
            'paragraphs' => $entity->getParagraphs(),
            'img'        => $entity->getImg(),
            'tags'       => $entity->getTags(),
            'categories' => $entity->getCategories(),
        ];
    }

    public function getEntityBySlug()
    {
        $request = $this->requestStack->getCurrentRequest();
        $slug    = $request->attributes->get('slug');
        $types   = $this->getPageByTypes();
        if ('' == $slug) {
            return $types['home'];
        }

        $page  = null;
        $types = array_filter(
            $types,
            fn ($type) => !is_null($type) && 'home' != $type->getType()
        );

        foreach ($types as $type => $row) {
            if ($slug == $row->getSlug()) {
                $page = $row;

                break;
            }

            if (str_contains((string) $slug, (string) $row->getSlug()) && str_starts_with((string) $slug, (string) $row->getSlug())) {
                $newslug = substr((string) $slug, strlen((string) $row->getSlug()) + 1);
                $page    = $this->getContentByType($type, $newslug);

                break;
            }
        }

        return $page;
    }

    public function getTypesPages()
    {
        return [
            'Home'      => 'home',
            'Posts'     => 'post',
            'Histoires' => 'history',
            'Page'      => 'page',
        ];
    }

    public function getViewByEntity(object $entity)
    {
        $reflectionClass = new ReflectionClass($entity);
        $entityName      = ucfirst($reflectionClass->getShortName());

        return $this->getViewByEntityName($entity, $entityName);
    }

    public function isEnable($entity): bool
    {
        return !(!$entity->isEnable() && is_null($this->getUser()));

        // TODO : Prévoir de vérifier les droits de l'utilisateur
    }

    protected function getMetaByEntity(Meta $meta)
    {
        return $meta;
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
        unset($entity);
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

    private function getContentByType(string $type, $slug)
    {
        if ('post' === $type) {
            return $this->postRepository->findOneBy(['slug' => $slug]);
        }

        $repos = [
            $this->chapterRepository,
            $this->historyRepository,
        ];
        $page = null;
        foreach ($repos as $repo) {
            $page = $repo->findOneBy(['slug' => $slug]);
            if (is_null($page)) {
                continue;
            }

            break;
        }

        return $page;
    }

    private function getPageByTypes()
    {
        $types = array_flip($this->getTypesPages());
        unset($types['page']);
        foreach (array_keys($types) as $type) {
            $types[$type] = $this->pageRepository->findOneBy(['type' => $type]);
        }

        return $types;
    }

    private function getUser()
    {
        $token = $this->tokenStorage->getToken();

        return is_null($token) ? null : $token->getUser();
    }
}
