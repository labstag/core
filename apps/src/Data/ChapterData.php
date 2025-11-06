<?php

namespace Labstag\Data;

use Doctrine\ORM\EntityManagerInterface;
use Labstag\Entity\Chapter;
use Labstag\Service\ConfigurationService;
use Labstag\Service\FileService;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Contracts\Translation\TranslatorInterface;

class ChapterData extends DataAbstract implements DataInterface
{
    public function __construct(
        protected StoryData $storyData,
        protected FileService $fileService,
        protected ConfigurationService $configurationService,
        protected EntityManagerInterface $entityManager,
        protected RequestStack $requestStack,
        protected TranslatorInterface $translator,
    )
    {
        parent::__construct($fileService, $configurationService, $entityManager, $requestStack, $translator);
    }

    #[\Override]
    public function asset(mixed $entity, string $field): string
    {
        $asset = parent::asset($entity, $field);
        if ('' !== $asset) {
            return $asset;
        }

        return $this->storyData->asset($entity->getStory(), $field);
    }

    public function generateSlug(object $entity): string
    {
        return $this->storyData->generateSlug($entity->getRefstory()) . '/' . $entity->getSlug();
    }

    public function getEntity(?string $slug): object
    {
        return $this->getEntityBySlug($slug);
    }

    public function getTitle(object $entity): string
    {
        return $entity->getTitle();
    }

    public function getTitleMeta(object $entity): string
    {
        return $this->storyData->getTitleMeta($entity->getRefstory()) . ' - ' . $this->getTitle($entity);
    }

    public function match(?string $slug): bool
    {
        $page = $this->getEntityBySlug($slug);

        return $page instanceof Chapter;
    }

    public function placeholder(): string
    {
        $placeholder = $this->globalPlaceholder('chapter');
        if ('' !== $placeholder) {
            return $placeholder;
        }

        return $this->storyData->placeholder();
    }

    public function supportsAsset(object $entity): bool
    {
        return $entity instanceof Chapter;
    }

    public function supportsData(object $entity): bool
    {
        return $entity instanceof Chapter;
    }

    public function supportsShortcode(string $className): bool
    {
        return false;
    }

    protected function getEntityBySlug(?string $slug): ?object
    {
        if (0 === substr_count((string) $slug, '/')) {
            return null;
        }

        $slugSecond = basename((string) $slug);
        $slugFirst  = dirname((string) $slug);

        if (false === $this->storyData->match($slugFirst)) {
            return null;
        }

        $story      = $this->storyData->getEntity($slugFirst);

        return $this->entityManager->getRepository(Chapter::class)->findOneBy(
            [
                'refstory' => $story,
                'slug'     => $slugSecond,
            ]
        );
    }
}
