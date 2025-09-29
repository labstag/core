<?php

namespace Labstag\Service;

use Exception;
use InvalidArgumentException;
use Labstag\Entity\Chapter;
use Labstag\Entity\Page;
use Labstag\Entity\Post;
use Labstag\Entity\Story;
use Labstag\Enum\PageEnum;
use Labstag\Repository\PageRepository;

class SlugService
{

    protected array $types = [];

    public function __construct(
        protected PageRepository $pageRepository,
    )
    {
    }

    public function forEntity(object $entity): string
    {
        $types = $this->getPageByTypes();

        return match (true) {
            $entity instanceof Page    => $entity->getSlug(),
            $entity instanceof Post    => $this->buildPrefixedSlug($types[PageEnum::POSTS->value], $entity->getSlug()),
            $entity instanceof Story   => $this->buildPrefixedSlug($types[PageEnum::STORIES->value], $entity->getSlug()),
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

    public function getPageByType(string $type): ?Page
    {
        $types = $this->getPageByTypes();

        return $types[$type] ?? null;
    }

    /**
     * Construit un slug préfixé avec validation de l'existence de la page type.
     */
    private function buildPrefixedSlug($page, string $suffix): string
    {
        if (!$page instanceof Page) {
            throw new Exception('No page found for this type');
        }

        return $page->getSlug() . '/' . $suffix;
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
