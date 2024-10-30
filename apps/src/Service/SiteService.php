<?php

namespace Labstag\Service;

use Exception;
use Labstag\Entity\Chapter;
use Labstag\Entity\Configuration;
use Labstag\Entity\History;
use Labstag\Entity\Meta;
use Labstag\Entity\Page;
use Labstag\Entity\Post;
use Labstag\Repository\ChapterRepository;
use Labstag\Repository\ConfigurationRepository;
use Labstag\Repository\HistoryRepository;
use Labstag\Repository\PageRepository;
use Labstag\Repository\PostRepository;
use ReflectionClass;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Twig\Environment;

class SiteService
{
    public function __construct(
        protected ChapterRepository $chapterRepository,
        protected TokenStorageInterface $tokenStorage,
        protected RequestStack $requestStack,
        protected HistoryRepository $historyRepository,
        protected PageRepository $pageRepository,
        protected PostRepository $postRepository,
        protected Environment $twigEnvironment,
        protected ConfigurationRepository $configurationRepository
    )
    {
    }

    public function getConfiguration()
    {
        $configurations = $this->configurationRepository->findAll();
        $tab            = [];
        foreach ($configurations as $configuration) {
            $data = $configuration->getValue();

            $tab[$configuration->getName()] = $data['value'];
        }

        return $tab;
    }

    public function getDataByEntity(object $entity)
    {
        $data = [
            'config'     => $this->getConfiguration(),
            'meta'       => $this->getMetaByEntity($entity->getMeta()),
            'paragraphs' => $entity->getParagraphs(),
            'img'        => $entity->getImg(),
            'tags'       => $entity->getTags(),
        ];

        $methods = get_class_methods($entity);
        if (in_array('getCategories', $methods)) {
            $data['categories'] = $entity->getCategories();
        }

        return $data;
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

        $page = $this->pageRepository->findOneBy(['slug' => $slug]);
        if ($page instanceof Page) {
            return $page;
        }

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

    public function getSlugByEntity($entity)
    {
        $types = $this->getPageByTypes();
        if ($entity instanceof Page) {
            return $entity->getSlug();
        }

        if ($entity instanceof Post) {
            if (is_null($types['post']) || !$types['post'] instanceof Page) {
                throw new Exception('Post page not found');
            }

            return $types['post']->getSlug().'/'.$entity->getSlug();
        }

        if ($entity instanceof History || $entity instanceof Chapter) {
            if (is_null($types['history']) || !$types['history'] instanceof Page) {
                throw new Exception('Post page not found');
            }

            return $types['history']->getSlug().'/'.$entity->getSlug();
        }

        return '';
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

    public function saveConfiguration($post)
    {
        foreach ($post as $name => $value) {
            $configuration = $this->configurationRepository->findOneBy(['name' => $name]);
            if (!$configuration instanceof Configuration) {
                $configuration = new Configuration();
                $configuration->setName($name);
            }

            $data = [
                'type'  => gettype($value),
                'value' => $value,
            ];

            $configuration->setValue($data);
            $this->configurationRepository->persist($configuration);
        }

        $this->configurationRepository->flush();
    }

    protected function getMetaByEntity(Meta $meta)
    {
        return $meta;
    }

    protected function getRepositories(): array
    {
        return [
            'chapter' => $this->chapterRepository,
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
