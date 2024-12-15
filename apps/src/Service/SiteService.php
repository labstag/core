<?php

namespace Labstag\Service;

use Exception;
use Labstag\Controller\Admin\ChapterCrudController;
use Labstag\Controller\Admin\StoryCrudController;
use Labstag\Controller\Admin\PageCrudController;
use Labstag\Controller\Admin\PostCrudController;
use Labstag\Entity\Chapter;
use Labstag\Entity\Configuration;
use Labstag\Entity\Story;
use Labstag\Entity\Meta;
use Labstag\Entity\Page;
use Labstag\Entity\Post;
use Labstag\Repository\BlockRepository;
use Labstag\Repository\ChapterRepository;
use Labstag\Repository\ConfigurationRepository;
use Labstag\Repository\StoryRepository;
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
        protected BlockRepository $blockRepository,
        protected BlockService $blockService,
        protected TokenStorageInterface $tokenStorage,
        protected RequestStack $requestStack,
        protected StoryRepository $storyRepository,
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

    public function getCrudController($entity)
    {
        $cruds  = $this->getDataCrudController();
        $return = null;
        foreach ($cruds as $object => $crud) {
            if ($object != $entity) {
                continue;
            }

            $return = $crud;
        }

        return $return;
    }

    public function getDataByEntity(object $entity)
    {
        $data = [
            'entity'     => $entity,
            'paragraphs' => $entity->getParagraphs()->getValues(),
            'img'        => $entity->getImg(),
            'tags'       => $entity->getTags(),
        ];

        $methods = get_class_methods($entity);
        if (in_array('getCategories', $methods)) {
            $data['categories'] = $entity->getCategories();
        }

        [
            $header,
            $main,
            $footer,
        ]       = $this->getBlocks($data);
        $blocks = array_merge(
            $header,
            $main,
            $footer
        );
        $contents = $this->blockService->getContents($blocks);

        return [
            'meta'   => $this->getMetaByEntity($entity->getMeta()),
            'blocks' => [
                'header' => $header,
                'main'   => $main,
                'footer' => $footer,
            ],
            'header' => $contents->header,
            'footer' => $contents->footer,
            'config' => $this->getConfiguration(),
            'data'   => $data,
        ];
    }

    public function getEntity()
    {
        $request = $this->requestStack->getCurrentRequest();
        $slug    = $request->attributes->get('slug');

        return $this->getEntityBySlug($slug);
    }

    public function getEntityBySlug($slug)
    {
        $types = $this->getPageByTypes();
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

    public function getPageByType($type)
    {
        $types = $this->getPageByTypes();

        return $types[$type] ?? null;
    }

    public function getSlugByEntity($entity)
    {
        $types = $this->getPageByTypes();
        $page  = $this->getSlugByEntityIfPage($entity);
        $page  = ('' == $page) ? $this->getSlugByEntityIfPost($types, $entity) : $page;
        $page  = ('' == $page) ? $this->getSlugByEntityIfStory($types, $entity) : $page;

        return ('' == $page) ? $this->getSlugByEntityIfChapter($types, $entity) : $page;
    }

    public function getTypesPages()
    {
        return [
            'Home'      => 'home',
            'Posts'     => 'post',
            'Histoires' => 'story',
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

    public function isHome($data)
    {
        return isset($data['entity']) && $data['entity'] instanceof Page && 'home' == $data['entity']->getType();
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

    public function setTitle($entity)
    {
        if ($entity instanceof Page) {
            return $entity->getTitle();
        }

        if ($entity instanceof Post) {
            return $entity->getTitle();
        }

        if ($entity instanceof Chapter) {
            return $this->setTitle($entity->getRefStory()).' - '.$entity->getTitle();
        }

        if ($entity instanceof Story) {
            return $entity->getTitle();
        }

        return '';
    }

    protected function getDataCrudController()
    {
        return [
            Story::class   => StoryCrudController::class,
            Chapter::class => ChapterCrudController::class,
            Page::class    => PageCrudController::class,
            Post::class    => PostCrudController::class,
        ];
    }

    protected function getMetaByEntity(Meta $meta)
    {
        return $meta;
    }

    protected function getRepositories(): array
    {
        return [
            'chapter' => $this->chapterRepository,
            'story'   => $this->storyRepository,
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

    private function getBlocks($data)
    {
        $query  = $this->blockRepository->findAllOrderedByRegion();
        $blocks = $query->getQuery()->getResult();
        $header = [];
        $main   = [];
        $footer = [];

        foreach ($blocks as $block) {
            if ('header' == $block->getRegion()) {
                $header[] = $block;
            } elseif ('main' == $block->getRegion()) {
                $main[] = $block;
            } elseif ('footer' == $block->getRegion()) {
                $footer[] = $block;
            }
        }

        return [
            $this->blockService->generate($header, $data),
            $this->blockService->generate($main, $data),
            $this->blockService->generate($footer, $data),
        ];
    }

    private function getContentByType(string $type, $slug)
    {
        if ('post' === $type) {
            return $this->postRepository->findOneBy(['slug' => $slug]);
        }

        $repos = [
            'story'   => $this->storyRepository,
            'chapter' => $this->chapterRepository,
        ];

        if (1 == substr_count((string) $slug, '/')) {
            [
                $slugstory,
                $slugchapter,
            ]        = explode('/', (string) $slug);
            $story = $repos['story']->findOneBy(['slug' => $slugstory]);
            $chapter = $repos['chapter']->findOneBy(['slug' => $slugchapter]);
            if ($story instanceof Story && $chapter instanceof Chapter && $story->getId() === $chapter->getRefStory()->getId()) {
                return $chapter;
            }
        }

        return $repos['story']->findOneBy(['slug' => $slug]);
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

    private function getSlugByEntityIfChapter($types, $entity)
    {
        if (!$entity instanceof Chapter) {
            return '';
        }

        if (is_null($types['story']) || !$types['story'] instanceof Page) {
            throw new Exception('Post page not found');
        }

        return $types['story']->getSlug().'/'.$entity->getRefStory()->getSlug().'/'.$entity->getSlug();
    }

    private function getSlugByEntityIfStory($types, $entity)
    {
        if (!$entity instanceof Story) {
            return '';
        }

        if (is_null($types['story']) || !$types['story'] instanceof Page) {
            throw new Exception('Post page not found');
        }

        return $types['story']->getSlug().'/'.$entity->getSlug();
    }

    private function getSlugByEntityIfPage($entity)
    {
        if (!$entity instanceof Page) {
            return '';
        }

        return $entity->getSlug();
    }

    private function getSlugByEntityIfPost($types, $entity)
    {
        if (!$entity instanceof Post) {
            return null;
        }

        if (is_null($types['post']) || !$types['post'] instanceof Page) {
            throw new Exception('Post page not found');
        }

        return $types['post']->getSlug().'/'.$entity->getSlug();
    }

    private function getUser()
    {
        $token = $this->tokenStorage->getToken();

        return is_null($token) ? null : $token->getUser();
    }
}
