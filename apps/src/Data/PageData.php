<?php

namespace Labstag\Data;

use Doctrine\ORM\EntityManagerInterface;
use Labstag\Entity\Page;
use Labstag\Service\ConfigurationService;
use Labstag\Service\FileService;
use Labstag\Shortcode\PageUrlShortcode;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Contracts\Translation\TranslatorInterface;

class PageData extends DataAbstract implements DataInterface
{
    public function __construct(
        protected HomeData $homeData,
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
        return $this->homeData->generateSlug($entity) . $entity->getSlug();
    }

    public function getEntity(?string $slug): object
    {
        return $this->getEntityBySlug($slug);
    }

    #[\Override]
    public function getShortCodes(): array
    {
        return [PageUrlShortcode::class];
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

        return $page instanceof Page;
    }

    public function placeholder(): string
    {
        $placeholder = $this->globalPlaceholder('page');
        if ('' !== $placeholder) {
            return $placeholder;
        }

        return $this->configPlaceholder();
    }

    public function supportsAsset(object $entity): bool
    {
        return $entity instanceof Page;
    }

    public function supportsData(object $entity): bool
    {
        return $entity instanceof Page;
    }

    public function supportsShortcode(string $className): bool
    {
        return Page::class === $className;
    }

    protected function generateShortcode1(string $id): string
    {
        return sprintf('[%s:%s]', 'pageurl', $id);
    }

    protected function getEntityBySlug(?string $slug): ?object
    {
        return $this->entityManager->getRepository(Page::class)->findOneBy(
            ['slug' => $slug]
        );
    }
}
