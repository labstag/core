<?php

namespace Labstag\Service;

use Exception;
use InvalidArgumentException;
use Labstag\Entity\Chapter;
use Labstag\Entity\Page;
use Labstag\Entity\Post;
use Labstag\Entity\Season;
use Labstag\Entity\Serie;
use Labstag\Entity\Story;
use Labstag\Enum\PageEnum;
use Labstag\Repository\ChapterRepository;
use Labstag\Repository\PageRepository;
use Labstag\Repository\PostRepository;
use Labstag\Repository\SeasonRepository;
use Labstag\Repository\SerieRepository;
use Labstag\Repository\StoryRepository;
use Symfony\Component\DependencyInjection\Attribute\AutowireIterator;
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
        /**
         * @var iterable<\Labstag\Data\Abstract\DataLib>
         */
        #[AutowireIterator('labstag.datas')]
        private readonly iterable $datalibs,
        private StoryRepository $storyRepository,
        private SeasonRepository $seasonRepository,
        private SerieRepository $serieRepository,
        private PageRepository $pageRepository,
        private ChapterRepository $chapterRepository,
        private PostRepository $postRepository,
        private RequestStack $requestStack,
    )
    {
    }

    public function forEntity(object $entity): string
    {
        foreach ($this->datalibs as $row) {
            if ($row->supports($entity)) {
                return $row->generateSlug($entity);
            }
        }

        throw new InvalidArgumentException(
            sprintf(
                'Unsupported entity type: %s',
                get_debug_type($entity)
            )
        );


        $types = $this->getPageByTypes();
        

        return match (true) {
            $entity instanceof Serie   => $this->buildPrefixedSlug(
                $types[PageEnum::SERIES->value],
                $entity->getSlug()
            ),
            $entity instanceof Season   => $this->buildPrefixedSlug(
                $types[PageEnum::SERIES->value],
                $entity->getRefserie()->getSlug().'/saison-'.$entity->getNumber()
            ),
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
        foreach ($this->datalibs as $row) {
            if ($row->match($slug)) {
                return $row->getEntity($slug);
            }
        }

        return null;
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
            'serie'   => $this->serieRepository,
            'story'   => $this->storyRepository,
            'season'  => $this->seasonRepository,
            'chapter' => $this->chapterRepository,
        ];

        if (1 === substr_count($slug, '/')) {
            [
                $slugFirst,
                $slugSecond,
            ]      = explode('/', $slug);
            $story = $repos['story']->findOneBy(
                ['slug' => $slugFirst]
            );
            $chapter = $repos['chapter']->findOneBy(
                ['slug' => $slugSecond]
            );
            $serie = $repos['serie']->findOneBy(
                ['slug' => $slugFirst]
            );
            $season = $repos['season']->findOneBy(
                [
                    'number' => str_replace('saison-', '', $slugSecond)
                ]
            );

            $data = [
                [
                    'test' => ($story instanceof Story && $chapter instanceof Chapter && $story->getId() === $chapter->getRefStory()->getId()),
                    'obj'  => $chapter,
                ],
                [
                    'test' => ($serie instanceof Serie && $season instanceof Season && $serie->getId() === $season->getRefserie()->getId()),
                    'obj'  => $season,
                ],
            ];
            foreach ($data as $row) {
                if ($row['test']) {
                    return $row['obj'];
                }
            }
        }

        $data = [
            $repos['story']->findOneBy(
                ['slug' => $slug]
            ),
            $repos['serie']->findOneBy(
                ['slug' => $slug]
            ),
        ];

        foreach ($data as $row) {
            if (is_object($row)) {
                return $row;
            }
        }

        return null;
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
