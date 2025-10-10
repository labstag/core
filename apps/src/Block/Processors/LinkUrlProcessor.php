<?php

namespace Labstag\Block\Processors;

use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Labstag\Entity\Page;
use Labstag\Entity\Post;
use Labstag\Entity\Story;
use Labstag\Repository\Abstract\ServiceEntityRepositoryLib;

class LinkUrlProcessor
{
    private const URL_PATTERNS = [
        '/\[pageurl:(.*?)]/'  => Page::class,
        '/\[posturl:(.*?)]/'  => Post::class,
        '/\[storyurl:(.*?)]/' => Story::class,
    ];

    /**
     * @var mixed[]
     */
    private array $entityCache = [];

    public function __construct(
        private EntityManagerInterface $entityManager,
    )
    {
    }

    /**
     * Process URL and return entity or original URL.
     */
    public function processUrl(string $url): string|object
    {
        foreach (self::URL_PATTERNS as $pattern => $entityClass) {
            if (preg_match($pattern, $url, $matches)) {
                $entity = $this->getEntity($entityClass, $matches[1]);

                return $entity ?? $url;
            }
        }

        return $url;
    }

    /**
     * Process multiple URLs at once with batch loading optimization.
     *
     * @param string[] $urls
     *
     * @return mixed[]
     */
    public function processUrls(array $urls): array
    {
        // Extract all entity IDs by type first
        $entityIds = $this->extractEntityIds($urls);

        // Batch load all entities
        $this->batchLoadEntities($entityIds);

        // Process each URL
        $result = [];
        foreach ($urls as $url) {
            $result[] = $this->processUrl($url);
        }

        return $result;
    }

    /**
     * @return ServiceEntityRepositoryLib<object>
     */
    protected function getRepository(string $entity): ServiceEntityRepositoryLib
    {
        $entityRepository = $this->entityManager->getRepository($entity);

        if (!$entityRepository instanceof ServiceEntityRepositoryLib) {
            throw new Exception('Repository not found for entity: ' . $entity);
        }

        return $entityRepository;
    }

    /**
     * Batch load entities by type to reduce database queries.
     *
     * @param mixed[] $entityIds
     */
    private function batchLoadEntities(array $entityIds): void
    {
        foreach ($entityIds as $entityClass => $ids) {
            if (empty($ids)) {
                continue;
            }

            $repository = $this->getRepository($entityClass);
            $entities   = $repository->findBy(
                [
                    'id' => array_unique($ids),
                ]
            );

            foreach ($entities as $entity) {
                $cacheKey                     = $entityClass . ':' . $entity->getId();
                $this->entityCache[$cacheKey] = $entity;
            }
        }
    }

    /**
     * Extract entity IDs grouped by type from URLs.
     *
     * @param string[] $urls
     *
     * @return mixed[]
     */
    private function extractEntityIds(array $urls): array
    {
        $entityIds = [];

        foreach ($urls as $url) {
            foreach (self::URL_PATTERNS as $pattern => $entityClass) {
                if (preg_match($pattern, $url, $matches)) {
                    $entityIds[$entityClass][] = $matches[1];
                }
            }
        }

        return $entityIds;
    }

    /**
     * Get entity from cache or database.
     */
    private function getEntity(string $entityClass, string $id): ?object
    {
        $cacheKey = $entityClass . ':' . $id;

        if (isset($this->entityCache[$cacheKey])) {
            return $this->entityCache[$cacheKey];
        }

        $serviceEntityRepositoryLib = $this->getRepository($entityClass);
        $entity                     = $serviceEntityRepositoryLib->find($id);

        $this->entityCache[$cacheKey] = $entity;

        return $entity;
    }
}
