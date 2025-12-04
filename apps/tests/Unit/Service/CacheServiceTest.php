<?php

declare(strict_types=1);

namespace Labstag\Tests\Unit\Service;

use Exception;
use Labstag\Service\CacheService;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Symfony\Contracts\Cache\CacheInterface;

final class CacheServiceTest extends TestCase
{
    private CacheInterface&MockObject $cache;

    private CacheService $cacheService;

    private LoggerInterface&MockObject $logger;

    #[Test]
    #[Group('unit')]
    #[Group('cache')]
    #[Group('fallback')]
    public function getFallsBackToCallbackOnCacheException(): void
    {
        // Arrange
        $key           = 'test_key';
        $callbackValue = 'callback_value';
        $ttl           = 3600;
        $callback      = fn (): string => $callbackValue;
        $exception     = new Exception('Cache error');

        $this->cache->method('get')
            ->with($key, $callback, $ttl)
            ->willThrowException($exception);

        $this->logger->expects($this->once())
            ->method('error')
            ->with('Cache error', [
                            'key'   => $key,
                            'error' => 'Cache error',
                        ]);

        // Act
        $result = $this->cacheService->get($key, $callback, $ttl);

        // Assert
        $this->assertEquals($callbackValue, $result);
    }

    #[Test]
    #[Group('unit')]
    #[Group('cache')]
    public function getReturnsValueFromCache(): void
    {
        // Arrange
        $key           = 'test_key';
        $expectedValue = 'cached_value';
        $ttl           = 3600;
        $callback      = fn (): string => 'callback_value';

        $this->cache->method('get')
            ->with($key, $callback, $ttl)
            ->willReturn($expectedValue);

        // Act
        $result = $this->cacheService->get($key, $callback, $ttl);

        // Assert
        $this->assertEquals($expectedValue, $result);
    }

    #[Test]
    #[Group('unit')]
    #[Group('cache')]
    #[Group('callback')]
    public function getWithComplexCallback(): void
    {
        // Arrange
        $key         = 'complex_key';
        $complexData = [
            'id'   => 123,
            'name' => 'Test Item',
            'data' => [
                'nested' => 'value',
            ],
        ];

        $callback = (fn (): array =>
            // Simulate an expensive operation
            $complexData);

        $this->cache->method('get')
            ->with($key, $callback, 3600)
            ->willReturn($complexData);

        // Act
        $result = $this->cacheService->get($key, $callback);

        // Assert
        $this->assertEquals($complexData, $result);
    }

    #[Test]
    #[Group('unit')]
    #[Group('cache')]
    #[Group('ttl')]
    public function getWithDefaultTtl(): void
    {
        // Arrange
        $key           = 'test_key';
        $expectedValue = 'cached_value';
        $defaultTtl    = 3600;
        $callback      = fn (): string => 'callback_value';

        $this->cache->method('get')
            ->with($key, $callback, $defaultTtl)
            ->willReturn($expectedValue);

        // Act
        $result = $this->cacheService->get($key, $callback);

        // Assert
        $this->assertEquals($expectedValue, $result);
    }

    #[Test]
    #[Group('unit')]
    #[Group('cache')]
    #[Group('ttl')]
    public function getWithDifferentTtl(): void
    {
        // Arrange
        $key           = 'test_key';
        $expectedValue = 'cached_value';
        $customTtl     = 7200;
        $callback      = fn (): string => 'callback_value';

        $this->cache->method('get')
            ->with($key, $callback, $customTtl)
            ->willReturn($expectedValue);

        // Act
        $result = $this->cacheService->get($key, $callback, $customTtl);

        // Assert
        $this->assertEquals($expectedValue, $result);
    }

    protected function setUp(): void
    {
        $this->cache        = $this->createMock(CacheInterface::class);
        $this->logger       = $this->createMock(LoggerInterface::class);
        $this->cacheService = new CacheService($this->cache, $this->logger);
    }
}
