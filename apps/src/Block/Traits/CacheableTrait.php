<?php

namespace Labstag\Block\Traits;

trait CacheableTrait
{

    /**
     * @var mixed[]
     */
    private array $cache = [];

    /**
     * Clear all cache entries.
     */
    protected function clearAllCache(): void
    {
        $this->cache = [];
    }

    /**
     * Clear specific cache entry.
     */
    protected function clearCache(string $key): void
    {
        unset($this->cache[$key]);
    }

    /**
     * Get cached value or compute and cache it.
     *
     * @param callable $callback Function to compute the value if not cached
     */
    protected function getCached(
        string $key,
        callable $callback,
    ): mixed
    {
        if (!isset($this->cache[$key])) {
            $this->cache[$key] = $callback();
        }

        return $this->cache[$key];
    }

    /**
     * Check if cache entry exists.
     */
    protected function hasCache(string $key): bool
    {
        return isset($this->cache[$key]);
    }
}
