<?php

namespace Labstag\Service;

use Exception;
use Psr\Log\LoggerInterface;
use Symfony\Contracts\Cache\CacheInterface;

class CacheService
{
    public function __construct(
        private CacheInterface $cache,
        private LoggerInterface $logger,
    )
    {
    }

    public function getOrSet(string $key, callable $callback, int $ttl = 3600): mixed
    {
        try {
            return $this->cache->get($key, $callback, $ttl);
        } catch (Exception $exception) {
            $this->logger->error(
                'Cache error',
                [
                    'key'   => $key,
                    'error' => $exception->getMessage(),
                ]
            );

            return $callback();
        }
    }

    public function invalidateTag(string $tag): void
    {
        $this->cache->invalidateTags([$tag]);
    }
}
