<?php

namespace Labstag\Service;

use Exception;
use Psr\Log\LoggerInterface;
use Symfony\Contracts\Cache\CacheInterface;

final class CacheService
{
    public function __construct(
        private CacheInterface $cache,
        private LoggerInterface $logger,
    )
    {
    }

    public function get(string $key, callable $callback, int $ttl = 60): mixed
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
}
