<?php

namespace Labstag\Data;

use Doctrine\ORM\EntityManagerInterface;
use Labstag\Entity\Page;
use Labstag\Entity\Story;
use Labstag\Enum\PageEnum;
use Labstag\Service\ConfigurationService;
use Labstag\Service\FileService;
use Labstag\Shortcode\StoryUrlShortcode;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Contracts\Translation\TranslatorInterface;

class StoryData extends DataAbstract implements DataInterface
{
    public function __construct(
        protected PageData $pageData,
        protected FileService $fileService,
        protected ConfigurationService $configurationService,
        protected EntityManagerInterface $entityManager,
        protected RequestStack $requestStack,
        protected TranslatorInterface $translator,
    )
    {
        parent::__construct($fileService, $configurationService, $entityManager, $requestStack, $translator);
    }

    public function generateSlug(object $entity): string
    {
        $page  = $this->entityManager->getRepository(Page::class)->findOneBy(
            [
                'type' => PageEnum::STORIES->value,
            ]
        );

        return $this->pageData->generateSlug($page) . '/' . $entity->getSlug();
    }

    public function getEntity(?string $slug): object
    {
        return $this->getEntityBySlug($slug);
    }

    #[\Override]
    public function getShortCodes(): array
    {
        return [StoryUrlShortcode::class];
    }

    public function getTitle(object $entity): string
    {
        return $entity->getTitle();
    }

    public function getTitleMeta(object $entity): string
    {
        return $this->getTitle($entity);
    }

    public function match(?string $slug): bool
    {
        $page = $this->getEntityBySlug($slug);

        return $page instanceof Story;
    }

    public function placeholder(): string
    {
        $placeholder = $this->globalPlaceholder('story');
        if ('' !== $placeholder) {
            return $placeholder;
        }

        return $this->configPlaceholder();
    }

    public function supportsAsset(object $entity): bool
    {
        return $entity instanceof Story;
    }

    public function supportsData(object $entity): bool
    {
        return $entity instanceof Story;
    }

    public function supportsShortcode(string $className): bool
    {
        return Story::class === $className;
    }

    protected function getEntityBySlug(?string $slug): ?object
    {
        if (0 === substr_count((string) $slug, '/')) {
            return null;
        }

        $slugSecond = basename((string) $slug);
        $slugFirst  = dirname((string) $slug);

        $page = $this->entityManager->getRepository(Page::class)->findOneBy(
            ['slug' => $slugFirst]
        );
        if (!$page instanceof Page) {
            return null;
        }

        if ($page->getType() != PageEnum::STORIES->value) {
            return null;
        }

        return $this->entityManager->getRepository(Story::class)->findOneBy(
            ['slug' => $slugSecond]
        );
    }
}
