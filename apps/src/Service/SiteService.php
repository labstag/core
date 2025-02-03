<?php

namespace Labstag\Service;

use Exception;
use Labstag\Controller\Admin\ChapterCrudController;
use Labstag\Controller\Admin\PageCrudController;
use Labstag\Controller\Admin\PostCrudController;
use Labstag\Controller\Admin\StoryCrudController;
use Labstag\Entity\Chapter;
use Labstag\Entity\Configuration;
use Labstag\Entity\Meta;
use Labstag\Entity\Page;
use Labstag\Entity\Post;
use Labstag\Entity\Story;
use Labstag\Repository\BlockRepository;
use Labstag\Repository\ChapterRepository;
use Labstag\Repository\ConfigurationRepository;
use Labstag\Repository\PageRepository;
use Labstag\Repository\PostRepository;
use Labstag\Repository\StoryRepository;
use ReflectionClass;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Twig\Environment;

class SiteService
{
    public function __construct(
        protected ChapterRepository $chapterRepository,
        protected FileService $fileService,
        protected ParagraphService $paragraphService,
        protected BlockRepository $blockRepository,
        protected BlockService $blockService,
        protected TokenStorageInterface $tokenStorage,
        protected RequestStack $requestStack,
        protected MetaService $metaService,
        protected StoryRepository $storyRepository,
        protected PageRepository $pageRepository,
        protected PostRepository $postRepository,
        protected Environment $twigEnvironment,
        protected ParameterBagInterface $parameterBag,
        protected ConfigurationRepository $configurationRepository,
    )
    {
    }

    public function asset(mixed $entity, string $field): string
    {
        $file = $this->fileService->asset($entity, $field);

        if ('' !== $file) {
            return $file;
        }

        if (!$entity instanceof Configuration) {
            $config = $this->getConfiguration();

            return $this->asset($config, 'placeholder');
        }

        return 'https://picsum.photos/1200/1200?md5=' . md5((string) $entity->getId());
    }

    public function getConfiguration(): ?Configuration
    {
        $configurations = $this->configurationRepository->findAll();

        return $configurations[0] ?? null;
    }

    public function getCrudController(string $entity): ?string
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

    /**
     * @return mixed[]
     */
    public function getDataByEntity(object $entity, bool $disable = false): array
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
        ]         = $this->getBlocks($data, $disable);
        $blocks   = array_merge($header, $main, $footer);
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

    public function getEntity(): ?object
    {
        $request = $this->requestStack->getCurrentRequest();
        $slug    = $request->attributes->get('slug');

        return $this->getEntityBySlug($slug);
    }

    public function getEntityBySlug(?string $slug): ?object
    {
        $types = $this->getPageByTypes();
        if ('' === $slug || is_null($slug)) {
            return $types['home'];
        }

        $page  = null;
        $types = array_filter($types, fn ($type): bool => !is_null($type) && 'home' != $type->getType());

        $page = $this->pageRepository->findOneBy(
            ['slug' => $slug]
        );
        if ($page instanceof Page) {
            return $page;
        }

        foreach ($types as $type => $row) {
            if ($slug == $row->getSlug()) {
                $page = $row;

                break;
            }

            if (str_contains($slug, (string) $row->getSlug()) && str_starts_with($slug, (string) $row->getSlug())) {
                $newslug = substr($slug, strlen((string) $row->getSlug()) + 1);
                $page    = $this->getContentByType($type, $newslug);

                break;
            }
        }

        return $page;
    }

    public function getFavicon(): ?array
    {
        $favicon = '';
        $file    = $this->fileService->getFileInAdapter('assets', 'manifest.json');
        $json    = json_decode(file_get_contents($file), true);
        foreach ($json as $title => $file) {
            if (0 === substr_count((string) $title, 'favicon')) {
                continue;
            }

            $favicon = $file;
        }

        if ('' == $favicon) {
            return null;
        }

        $favicon = str_replace('/assets/', '', $favicon);
        $favicon = $this->fileService->getFileInAdapter('assets', $favicon);
        if (is_null($favicon)) {
            return null;
        }

        return $this->fileService->getInfoImage($favicon);
    }

    public function getImageForMetatags(mixed $entity): ?array
    {
        $file = $this->asset($entity, 'img');
        if (null == $file) {
            return null;
        }

        $file = str_replace('/uploads/', '', $file);
        $file = $this->fileService->getFileInAdapter('public', $file);

        return $this->fileService->getInfoImage($file);
    }

    public function getMetatags(object $entity): Meta
    {
        $meta = $entity->getMeta();
        if ($meta instanceof Meta) {
            $meta = new Meta();
        }

        if (!is_null($meta->getDescription()) && '' !== $meta->getDescription() && '0' !== $meta->getDescription()) {
            return $meta;
        }

        $html = $this->twigEnvironment->render('metagenerate.html.twig', $this->getDataByEntity($entity, true));

        $html = preg_replace('/\s+/', ' ', $html);

        $text = trim(strip_tags((string) $html));
        $text = substr($text, 0, 256);

        $meta->setDescription($text);

        return $meta;
    }

    public function getPageByType(string $type): ?Page
    {
        $types = $this->getPageByTypes();

        return $types[$type] ?? null;
    }

    public function getSlugByEntity(object $entity): string
    {
        $types = $this->getPageByTypes();
        $page  = $this->getSlugByEntityIfPage($entity);
        $page  = ('' == $page) ? $this->getSlugByEntityIfPost($types, $entity) : $page;
        $page  = ('' == $page) ? $this->getSlugByEntityIfStory($types, $entity) : $page;

        return ('' === $page) ? $this->getSlugByEntityIfChapter($types, $entity) : $page;
    }

    /**
     * @return mixed[]
     */
    public function getTypesPages(): array
    {
        return [
            'Home'      => 'home',
            'Posts'     => 'post',
            'Movie'     => 'movie',
            'Histoires' => 'story',
            'Page'      => 'page',
        ];
    }

    public function getViewByEntity(object $entity): string
    {
        $reflectionClass = new ReflectionClass($entity);
        $entityName      = ucfirst($reflectionClass->getShortName());

        return $this->getViewByEntityName($entity, $entityName);
    }

    public function isEnable(object $entity): bool
    {
        return !(!$entity->isEnable() && !$this->getUser() instanceof UserInterface);

        // TODO : Prévoir de vérifier les droits de l'utilisateur
    }

    /**
     * @param mixed[] $data
     */
    public function isHome(array $data): bool
    {
        return isset($data['entity']) && $data['entity'] instanceof Page && 'home' == $data['entity']->getType();
    }

    public function setTitle(object $entity): ?string
    {
        if ($entity instanceof Chapter) {
            return $this->setTitle($entity->getRefStory()) . ' - ' . $entity->getTitle();
        }

        if (method_exists($entity, 'getTitle')) {
            return $entity->getTitle();
        }

        return '';
    }

    /**
     * @return mixed[]
     */
    protected function getDataCrudController(): array
    {
        return [
            Story::class   => StoryCrudController::class,
            Chapter::class => ChapterCrudController::class,
            Page::class    => PageCrudController::class,
            Post::class    => PostCrudController::class,
        ];
    }

    protected function getMetaByEntity(Meta $meta): Meta
    {
        return $meta;
    }

    protected function getViewByEntityName(object $entity, string $entityName): string
    {
        unset($entity);
        $loader = $this->twigEnvironment->getLoader();
        $files  = [
            'views/' . $entityName . '.html.twig',
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

    /**
     * @param mixed[] $data
     *
     * @return mixed[]
     */
    private function getBlocks(array $data, bool $disable): array
    {
        $queryBuilder = $this->blockRepository->findAllOrderedByRegion();
        $blocks       = $queryBuilder->getQuery()->getResult();
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
            $this->blockService->generate($header, $data, $disable),
            $this->blockService->generate($main, $data, $disable),
            $this->blockService->generate($footer, $data, $disable),
        ];
    }

    private function getContentByType(string $type, string $slug): ?object
    {
        if ('post' === $type) {
            return $this->postRepository->findOneBy(
                ['slug' => $slug]
            );
        }

        $repos = [
            'story'   => $this->storyRepository,
            'chapter' => $this->chapterRepository,
        ];

        if (1 == substr_count($slug, '/')) {
            [
                $slugstory,
                $slugchapter,
            ]      = explode('/', $slug);
            $story = $repos['story']->findOneBy(
                ['slug' => $slugstory]
            );
            $chapter = $repos['chapter']->findOneBy(
                ['slug' => $slugchapter]
            );
            if ($story instanceof Story && $chapter instanceof Chapter && $story->getId() === $chapter->getRefStory()->getId()) {
                return $chapter;
            }
        }

        return $repos['story']->findOneBy(
            ['slug' => $slug]
        );
    }

    /**
     * @return mixed[]
     */
    private function getPageByTypes(): array
    {
        $types = array_flip($this->getTypesPages());
        unset($types['page']);
        foreach (array_keys($types) as $type) {
            $types[$type] = $this->pageRepository->findOneBy(
                ['type' => $type]
            );
        }

        return $types;
    }

    /**
     * @param mixed[] $types
     */
    private function getSlugByEntityIfChapter(array $types, object $entity): string
    {
        if (!$entity instanceof Chapter) {
            return '';
        }

        if (is_null($types['story']) || !$types['story'] instanceof Page) {
            throw new Exception('Story page not found');
        }

        return $types['story']->getSlug() . '/' . $entity->getRefStory()->getSlug() . '/' . $entity->getSlug();
    }

    private function getSlugByEntityIfPage(object $entity): ?string
    {
        if (!$entity instanceof Page) {
            return '';
        }

        return $entity->getSlug();
    }

    /**
     * @param mixed[] $types
     */
    private function getSlugByEntityIfPost(array $types, object $entity): ?string
    {
        if (!$entity instanceof Post) {
            return null;
        }

        if (is_null($types['post']) || !$types['post'] instanceof Page) {
            throw new Exception('Post page not found');
        }

        return $types['post']->getSlug() . '/' . $entity->getSlug();
    }

    /**
     * @param mixed[] $types
     */
    private function getSlugByEntityIfStory(array $types, object $entity): string
    {
        if (!$entity instanceof Story) {
            return '';
        }

        if (is_null($types['story']) || !$types['story'] instanceof Page) {
            throw new Exception('Story page not found');
        }

        return $types['story']->getSlug() . '/' . $entity->getSlug();
    }

    private function getUser(): ?UserInterface
    {
        $token = $this->tokenStorage->getToken();

        return $token instanceof TokenInterface ? $token->getUser() : null;
    }
}
