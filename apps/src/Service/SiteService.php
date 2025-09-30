<?php

namespace Labstag\Service;

use Labstag\Controller\Admin\ChapterCrudController;
use Labstag\Controller\Admin\PageCrudController;
use Labstag\Controller\Admin\PostCrudController;
use Labstag\Controller\Admin\StoryCrudController;
use Labstag\Entity\Chapter;
use Labstag\Entity\Configuration;
use Labstag\Entity\Page;
use Labstag\Entity\Post;
use Labstag\Entity\Story;
use Labstag\Enum\PageEnum;
use Labstag\Repository\BlockRepository;
use Labstag\Repository\ChapterRepository;
use Labstag\Repository\ConfigurationRepository;
use Labstag\Repository\PageRepository;
use Labstag\Repository\PostRepository;
use Labstag\Repository\StoryRepository;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Twig\Environment;

class SiteService
{

    protected ?Configuration $configuration = null;

    protected array $pages = [];

    protected array $types = [];

    public function __construct(
        protected ConfigurationService $configurationService,
        protected ViewResolverService $viewResolverService,
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
        protected ConfigurationRepository $configurationRepository,
    )
    {
    }

    public function asset(mixed $entity, string $field, bool $placeholder = true): string
    {
        $file = $this->fileService->asset($entity, $field);

        if ('' !== $file) {
            return $file;
        }

        if (!$placeholder) {
            return '';
        }

        if (!$entity instanceof Configuration) {
            $config = $this->configurationService->getConfiguration();

            return $this->asset($config, 'placeholder');
        }

        return 'https://picsum.photos/1200/1200?md5=' . md5((string) $entity->getId());
    }

    public function getCrudController(string $entity): ?string
    {
        $tab = [
            Story::class   => StoryCrudController::class,
            Chapter::class => ChapterCrudController::class,
            Page::class    => PageCrudController::class,
            Post::class    => PostCrudController::class,
        ];

        return $tab[$entity] ?? null;
    }

    public function getFileFavicon(): ?array
    {
        $favicon = $this->getFavicon('favicon.ico');

        return is_null($favicon) ? $this->getFavicon('favicon') : $favicon;
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
        return isset($data['entity']) && $data['entity'] instanceof Page && PageEnum::HOME->value == $data['entity']->getType();
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

    private function getFavicon(string $type): ?array
    {
        $info = null;
        $file = $this->fileService->getFileInAdapter('assets', 'manifest.json');
        $json = json_decode(file_get_contents($file), true);
        foreach ($json as $title => $file) {
            $info = null;
            if (0 === substr_count((string) $title, $type)) {
                continue;
            }

            $file          = str_replace('/assets/', '', $file);
            $fileInAdapter = $this->fileService->getFileInAdapter('assets', $file);
            if (is_null($fileInAdapter)) {
                continue;
            }

            $info = $this->fileService->getInfoImage($fileInAdapter);
            if (!is_array($info['data'])) {
                continue;
            }

            if (0 == substr_count((string) $info['data']['type'], 'image')) {
                continue;
            }

            break;
        }

        if (is_null($info)) {
            return null;
        }

        return $info;
    }

    private function getUser(): ?UserInterface
    {
        $token = $this->tokenStorage->getToken();

        return $token instanceof TokenInterface ? $token->getUser() : null;
    }
}
