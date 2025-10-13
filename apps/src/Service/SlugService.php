<?php

namespace Labstag\Service;

use Exception;
use InvalidArgumentException;
use Labstag\Entity\Chapter;
use Labstag\Entity\Page;
use Labstag\Entity\Post;
use Labstag\Entity\Story;
use Labstag\Enum\PageEnum;
use Labstag\Repository\ChapterRepository;
use Labstag\Repository\PageRepository;
use Labstag\Repository\PostRepository;
use Labstag\Repository\StoryRepository;
use Symfony\Component\HttpFoundation\RequestStack;

final class SlugService
{

    /**
     * @var array<string, mixed>
     */
    private array $pages = [];

    /**
     * @var array<string, mixed>
     */
    private array $types = [];

    public function __construct(
        private StoryRepository $storyRepository,
        private PageRepository $pageRepository,
        private ChapterRepository $chapterRepository,
        private PostRepository $postRepository,
        private RequestStack $requestStack,
    )
    {
    }

    public function forEntity(object $entity): string
    {
        $types = $this->getPageByTypes();

        return match (true) {
            $entity instanceof Page    => $entity->getSlug(),
            $entity instanceof Post    => $this->buildPrefixedSlug($types[PageEnum::POSTS->value], $entity->getSlug()),
            $entity instanceof Story   => $this->buildPrefixedSlug(
                $types[PageEnum::STORIES->value],
                $entity->getSlug()
            ),
            $entity instanceof Chapter => $this->buildPrefixedSlug(
                $types[PageEnum::STORIES->value],
                $entity->getRefStory()->getSlug() . '/' . $entity->getSlug()
            ),
            default => throw new InvalidArgumentException(
                sprintf(
                    'Unsupported entity type: %s',
                    get_debug_type($entity)
                )
            ),
        };
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
            return $types[PageEnum::HOME->value];
        }

        $page  = null;
        $types = array_filter($types, fn ($type): bool => !is_null($type) && PageEnum::HOME->value != $type->getType());

        $page = $this->getPageBySlug($slug);
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

    public function getPageByType(string $type): ?Page
    {
        $types = $this->getPageByTypes();

        return $types[$type] ?? null;
    }

    /**
     * Construit un slug préfixé avec validation de l'existence de la page type.
     */
    private function buildPrefixedSlug(object $page, string $suffix): string
    {
        if (!$page instanceof Page) {
            throw new Exception('No page found for this type');
        }

        return $page->getSlug() . '/' . $suffix;
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

        if (1 === substr_count($slug, '/')) {
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

    private function getPageBySlug(string $slug): ?Page
    {
        if (array_key_exists($slug, $this->pages)) {
            return $this->pages[$slug];
        }

        $page               = $this->pageRepository->getOneBySlug($slug);
        $this->pages[$slug] = $page;

        return $page;
    }

    /**
     * @return mixed[]
     */
    private function getPageByTypes(): array
    {
        if ([] !== $this->types) {
            return $this->types;
        }

        $types = [];
        $data  = PageEnum::cases();
        foreach ($data as $row) {
            if ($row->value == PageEnum::PAGE->value) {
                continue;
            }

            $types[$row->value] = $this->pageRepository->getOneByType($row->value);
        }

        $this->types = $types;

        return $types;
    }
}
