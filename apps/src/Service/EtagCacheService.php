<?php

namespace Labstag\Service;

use DateTime;
use DateTimeInterface;
use Exception;
use ReflectionClass;
use ReflectionException;

final class EtagCacheService
{

    /**
     * Cache des ETags calculés pour éviter les recalculs multiples
     * durant la même requête.
     *
     * @var array<string, string>
     */
    private array $etagCache = [];

    /**
     * Cache des dernières modifications pour éviter les recalculs multiples
     * durant la même requête.
     *
     * @var array<string, DateTimeInterface|null>
     */
    private array $lastModifiedCache = [];

    /**
     * Vide complètement le cache interne.
     */
    public function clearCache(): void
    {
        $this->etagCache         = [];
        $this->lastModifiedCache = [];
    }

    /**
     * Génère un ETag pour une collection d'entités.
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

        // Ajouter le count pour détecter les changements de taille
        $etags[] = (string) count($entities);

        return sha1(implode('|', $etags));
    }

    /**
     * Génère un ETag unique pour une entité donnée.
     *
     * L'ETag est basé sur :
     * - Le nom de la classe de l'entité
     * - L'ID de l'entité (si disponible)
     * - La date de dernière modification (si disponible)
     * - Un hash des propriétés critiques de l'entité
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
     * Génère tous les headers de cache nécessaires pour une entité.
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
     * Récupère la date de dernière modification d'une entité.
     *
     * Priorité :
     * 1. updatedAt (si disponible et non null)
     * 2. createdAt (si disponible et non null)
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
     * Génère des headers de cache optimisés selon le type d'entité.
     */
    public function getOptimizedCacheHeaders(object $entity): array
    {
        $baseHeaders = $this->getCacheHeaders($entity);

        // Personnaliser la durée de cache selon le type d'entité
        $maxAge                                  = $this->getOptimalCacheTime($entity);
        $baseHeaders['headers']['Cache-Control'] = sprintf('public, max-age=%d, must-revalidate', $maxAge);

        return $baseHeaders;
    }

    /**
     * Invalide le cache pour une entité donnée.
     */
    public function invalidateCache(object $entity): void
    {
        $cacheKey = $this->getCacheKey($entity);

        unset($this->etagCache[$cacheKey]);
        unset($this->lastModifiedCache[$cacheKey]);
    }

    /**
     * Vérifie si une entité a été modifiée depuis un ETag donné.
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
     * Vérifie si une entité a été modifiée depuis une date donnée.
     */
    public function isModifiedSinceDate(object $entity, DateTimeInterface $ifModifiedSince): bool
    {
        $lastModified = $this->getLastModified($entity);

        if (!$lastModified instanceof DateTimeInterface) {
            return true;
            // Si on ne peut pas déterminer, considérer comme modifié
        }

        return $lastModified > $ifModifiedSince;
    }

    /**
     * Vérifie si une réponse 304 Not Modified peut être envoyée.
     */
    public function shouldSendNotModified(
        object $entity,
        ?string $ifNoneMatch = null,
        ?string $ifModifiedSince = null,
    ): bool
    {
        // Vérification ETag
        if (null !== $ifNoneMatch && !$this->isModifiedSince($entity, $ifNoneMatch)) {
            return true;
        }

        // Vérification If-Modified-Since
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
     * Construit les parties de l'ETag pour une entité.
     *
     * @return string[]
     */
    private function buildEtagParts(object $entity): array
    {
        $parts = [$entity::class];

        // Ajouter l'ID si disponible
        $id = $this->extractEntityId($entity);
        if (null !== $id) {
            $parts[] = (string) $id;
        }

        // Ajouter le timestamp de dernière modification
        $lastModified = $this->extractLastModified($entity);
        if ($lastModified instanceof DateTimeInterface) {
            $parts[] = (string) $lastModified->getTimestamp();
        }

        // Ajouter un hash des propriétés critiques
        $criticalProperties = $this->extractCriticalProperties($entity);
        if ([] !== $criticalProperties) {
            $parts[] = md5(serialize($criticalProperties));
        }

        return $parts;
    }

    /**
     * Extrait les propriétés critiques d'une entité pour la génération d'ETag.
     * Ces propriétés influencent le rendu et doivent déclencher un changement d'ETag.
     *
     * @return array<string, mixed>
     */
    private function extractCriticalProperties(object $entity): array
    {
        $properties = [];

        try {
            $reflectionClass = new ReflectionClass($entity);

            // Propriétés critiques communes
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
                    // Sérialiser seulement les valeurs scalaires pour éviter les problèmes
                    if (is_scalar($value) || is_null($value)) {
                        $properties[$criticalMethod] = $value;
                    }
                }
            }
        } catch (ReflectionException) {
            throw new Exception('Reflection error on entity: ' . $entity::class);
            // En cas d'erreur de réflection, continuer sans les propriétés
        }

        return $properties;
    }

    /**
     * Extrait l'ID d'une entité si disponible.
     */
    private function extractEntityId(object $entity): mixed
    {
        if (method_exists($entity, 'getId')) {
            return $entity->getId();
        }

        return null;
    }

    /**
     * Extrait la date de dernière modification d'une entité.
     */
    private function extractLastModified(object $entity): ?DateTimeInterface
    {
        // Priorité à updatedAt
        if (method_exists($entity, 'getUpdatedAt')) {
            $updatedAt = $entity->getUpdatedAt();
            if ($updatedAt instanceof DateTimeInterface) {
                return $updatedAt;
            }
        }

        // Fallback sur createdAt
        if (method_exists($entity, 'getCreatedAt')) {
            $createdAt = $entity->getCreatedAt();
            if ($createdAt instanceof DateTimeInterface) {
                return $createdAt;
            }
        }

        return null;
    }

    /**
     * Génère une clé de cache unique pour une entité.
     */
    private function getCacheKey(object $entity): string
    {
        $className = $entity::class;
        $id        = $this->extractEntityId($entity);

        return $className . ':' . ($id ?? 'no-id') . ':' . spl_object_hash($entity);
    }

    /**
     * Détermine la durée de cache optimale selon le type d'entité.
     */
    private function getOptimalCacheTime(object $entity): int
    {
        // Configuration par défaut selon le type d'entité
        $cacheTimings = [
            'Configuration' => 86400,
            // 24h pour la configuration
            'Page'          => 7200,
            // 2h pour les pages
            'Post'          => 3600,
            // 1h pour les posts
            'Story'         => 3600,
            // 1h pour les stories
            'Chapter'       => 1800,
            // 30min pour les chapitres
            'Movie'         => 7200,
            // 2h pour les films
        ];

        // Extraire le nom de classe simple
        $reflectionClass     = new ReflectionClass($entity);
        $shortClassName      = $reflectionClass->getShortName();

        return $cacheTimings[$shortClassName] ?? 3600;
        // 1h par défaut
    }
}
