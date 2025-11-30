<?php

namespace Labstag\Service;

use DateTime;
use DateTimeInterface;
use Exception;
use ReflectionClass;
use ReflectionException;

final class EtagCacheService
{
    private const TIME_DAY       = 86400;

    private const TIME_HALF_HOUR = 1800;

    private const TIME_HOUR      = 3600;

    private const TIME_TWO_HOURS = 7200;

    /**
     * Cache of calculated ETags to avoid multiple recalculations
     * during the same request.
     *
     * @var array<string, string>
     */
    private array $etagCache = [];

    /**
     * Cache of last modifications to avoid multiple recalculations
     * during the same request.
     *
     * @var array<string, DateTimeInterface|null>
     */
    private array $lastModifiedCache = [];

    /**
     * Completely clears the internal cache.
     */
    public function clearCache(): void
    {
        $this->etagCache         = [];
        $this->lastModifiedCache = [];
    }

    /**
     * Generates an ETag for a collection of entities.
     *
     * @param object[] $entities
     */
    public function generateCollectionEtag(array $entities): string
    {
        if ([] === $entities) {
            return sha1('empty-collection');
        }

        $etags = [];
        foreach ($entities as $entity) {
            $etags[] = $this->generateEtag($entity);
        }

        // Add count to detect size changes
        $etags[] = (string) count($entities);

        return sha1(implode('|', $etags));
    }

    /**
     * Generates a unique ETag for a given entity.
     *
     * The ETag is based on:
     * - The entity's class name
     * - The entity's ID (if available)
     * - The last modification date (if available)
     * - A hash of the entity's critical properties
     */
    public function generateEtag(object $entity): string
    {
        $cacheKey = $this->getCacheKey($entity);

        if (isset($this->etagCache[$cacheKey])) {
            return $this->etagCache[$cacheKey];
        }

        $etagParts = $this->buildEtagParts($entity);
        $etag      = sha1(implode('|', $etagParts));

        $this->etagCache[$cacheKey] = $etag;

        return $etag;
    }

    /**
     * Generates all necessary cache headers for an entity.
     *
     * @return array{etag: string, lastModified: DateTimeInterface|null, headers: array<string, string>}
     */
    public function getCacheHeaders(object $entity): array
    {
        $etag         = $this->generateEtag($entity);
        $lastModified = $this->getLastModified($entity);

        $headers = [
            'Cache-Control' => 'public, max-age=3600, must-revalidate',
            'ETag'          => '"' . $etag . '"',
        ];

        if ($lastModified instanceof DateTimeInterface) {
            $headers['Last-Modified'] = $lastModified->format('D, d M Y H:i:s') . ' GMT';
        }

        return [
            'etag'         => $etag,
            'lastModified' => $lastModified,
            'headers'      => $headers,
        ];
    }

    /**
     * Retrieves the last modification date of an entity.
     *
     * Priority:
     * 1. updatedAt (if available and not null)
     * 2. createdAt (if available and not null)
     * 3. null
     */
    public function getLastModified(object $entity): ?DateTimeInterface
    {
        $cacheKey = $this->getCacheKey($entity);

        if (isset($this->lastModifiedCache[$cacheKey])) {
            return $this->lastModifiedCache[$cacheKey];
        }

        $lastModified                       = $this->extractLastModified($entity);
        $this->lastModifiedCache[$cacheKey] = $lastModified;

        return $lastModified;
    }

    /**
     * Generates optimized cache headers according to entity type.
     *
     * @return array{etag: string, lastModified: DateTimeInterface|null, headers: array<string, string>}
     */
    public function getOptimizedCacheHeaders(object $entity): array
    {
        $baseHeaders = $this->getCacheHeaders($entity);

        $maxAge                                  = $this->getOptimalCacheTime($entity);
        $baseHeaders['headers']['Cache-Control'] = sprintf('public, max-age=%d, must-revalidate', $maxAge);

        return $baseHeaders;
    }

    /**
     * Invalidates the cache for a given entity.
     */
    public function invalidateCache(object $entity): void
    {
        $cacheKey = $this->getCacheKey($entity);

        unset($this->etagCache[$cacheKey]);
        unset($this->lastModifiedCache[$cacheKey]);
    }

    /**
     * Checks if an entity has been modified since a given ETag.
     */
    public function isModifiedSince(object $entity, string $clientEtag): bool
    {
        $currentEtag = $this->generateEtag($entity);

        // Nettoyer les ETags des guillemets potentiels
        $clientEtag  = trim($clientEtag, '"');
        $currentEtag = trim($currentEtag, '"');

        return $clientEtag !== $currentEtag;
    }

    /**
     * Checks if an entity has been modified since a given date.
     */
    public function isModifiedSinceDate(object $entity, DateTimeInterface $ifModifiedSince): bool
    {
        $lastModified = $this->getLastModified($entity);

        if (!$lastModified instanceof DateTimeInterface) {
            return true;
            // If we can't determine, consider as modified
        }

        return $lastModified > $ifModifiedSince;
    }

    /**
     * Checks if a 304 Not Modified response can be sent.
     */
    public function shouldSendNotModified(
        object $entity,
        ?string $ifNoneMatch = null,
        ?string $ifModifiedSince = null,
    ): bool
    {
        // ETag verification
        if (null !== $ifNoneMatch && !$this->isModifiedSince($entity, $ifNoneMatch)) {
            return true;
        }

        // If-Modified-Since verification
        if (null !== $ifModifiedSince) {
            try {
                $ifModifiedSinceDate = new DateTime($ifModifiedSince);
                if (!$this->isModifiedSinceDate($entity, $ifModifiedSinceDate)) {
                    return true;
                }
            } catch (Exception) {
                throw new Exception('Invalid If-Modified-Since date: ' . $ifModifiedSince);
                // Date invalide, ignorer
            }
        }

        return false;
    }

    /**
     * Builds the ETag parts for an entity.
     *
     * @return string[]
     */
    private function buildEtagParts(object $entity): array
    {
        $parts = [$entity::class];

        // Add ID if available
        $id = $this->extractEntityId($entity);
        if (null !== $id) {
            $parts[] = (string) $id;
        }

        // Add last modification timestamp
        $lastModified = $this->extractLastModified($entity);
        if ($lastModified instanceof DateTimeInterface) {
            $parts[] = (string) $lastModified->getTimestamp();
        }

        // Add hash of critical properties
        $criticalProperties = $this->extractCriticalProperties($entity);
        if ([] !== $criticalProperties) {
            $parts[] = md5(serialize($criticalProperties));
        }

        return $parts;
    }

    /**
     * Extracts critical properties of an entity for ETag generation.
     * These properties influence rendering and should trigger an ETag change.
     *
     * @return array<string, mixed>
     */
    private function extractCriticalProperties(object $entity): array
    {
        $properties = [];

        try {
            $reflectionClass = new ReflectionClass($entity);

            // Common critical properties
            $criticalMethods = [
                'getTitle',
                'getSlug',
                'getContent',
                'getDescription',
                'isEnable',
                'getPosition',
                'getState',
                'getStatus',
            ];

            foreach ($criticalMethods as $criticalMethod) {
                if ($reflectionClass->hasMethod($criticalMethod)) {
                    $value = $entity->{$criticalMethod}();
                    // Serialize only scalar values to avoid problems
                    if (is_scalar($value) || is_null($value)) {
                        $properties[$criticalMethod] = $value;
                    }
                }
            }
        } catch (ReflectionException) {
            throw new Exception('Reflection error on entity: ' . $entity::class);
        }

        return $properties;
    }

    /**
     * Extracts the ID of an entity if available.
     */
    private function extractEntityId(object $entity): mixed
    {
        if (method_exists($entity, 'getId')) {
            return $entity->getId();
        }

        return null;
    }

    /**
     * Extracts the last modification date of an entity.
     */
    private function extractLastModified(object $entity): ?DateTimeInterface
    {
        // Priority to updatedAt
        if (method_exists($entity, 'getUpdatedAt')) {
            $updatedAt = $entity->getUpdatedAt();
            if ($updatedAt instanceof DateTimeInterface) {
                return $updatedAt;
            }
        }

        // Fallback to createdAt
        if (method_exists($entity, 'getCreatedAt')) {
            $createdAt = $entity->getCreatedAt();
            if ($createdAt instanceof DateTimeInterface) {
                return $createdAt;
            }
        }

        return null;
    }

    /**
     * Generates a unique cache key for an entity.
     */
    private function getCacheKey(object $entity): string
    {
        $className = $entity::class;
        $id        = $this->extractEntityId($entity);

        return $className . ':' . ($id ?? 'no-id') . ':' . spl_object_hash($entity);
    }

    /**
     * Determines the optimal cache duration according to entity type.
     */
    private function getOptimalCacheTime(object $entity): int
    {
        // Default configuration according to entity type
        $cacheTimings = [
            'Configuration' => self::TIME_DAY,
            // 24h for configuration
            'Page'          => self::TIME_TWO_HOURS,
            // 2h for pages
            'Post'          => self::TIME_HOUR,
            // 1h for posts
            'Story'         => self::TIME_HOUR,
            // 1h for stories
            'Chapter'       => self::TIME_HALF_HOUR,
            // 30min for chapters
            'Movie'         => self::TIME_TWO_HOURS,
            // 2h for movies
        ];

        // Extract simple class name
        $reflectionClass     = new ReflectionClass($entity);
        $shortClassName      = $reflectionClass->getShortName();

        return $cacheTimings[$shortClassName] ?? self::TIME_HOUR;
        // 1h by default
    }
}
