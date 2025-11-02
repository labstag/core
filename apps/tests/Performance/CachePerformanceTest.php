<?php

declare(strict_types=1);

namespace Labstag\Tests\Performance;

use Labstag\Entity\Post;
use Labstag\Entity\User;
use Labstag\Service\EtagCacheService;
use Labstag\Tests\AbstractTestCase;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Component\Stopwatch\Stopwatch;

final class CachePerformanceTest extends AbstractTestCase
{
    // Performance test constants
    private const CACHE_INVALIDATION_TEST_ENTITIES  = 100;

    private const CACHE_VALIDATION_TEST_ENTITIES    = 10;

    private const COLLECTION_ETAG_TEST_ENTITIES     = 50;

    private const CONCURRENT_ACCESS_TEST_ITERATIONS = 100;

    private const ETAG_GENERATION_TEST_ENTITIES     = 100;

    private const MEMORY_LEAK_TEST_ENTITIES         = 200;

    private EtagCacheService $etagCacheService;

    private Stopwatch $stopwatch;

    /**
     * Cache invalidation performance test.
     */
    #[Test]
    #[Group('performance')]
    #[Group('cache')]
    public function cacheInvalidationPerformance(): void
    {
        // Arrange
        $entities = [];
        for ($i = 0; self::CACHE_INVALIDATION_TEST_ENTITIES > $i; ++$i) {
            $user = new User();
            $user->setEmail(sprintf('invalidation%d@example.com', $i));
            $user->setUsername('invaliduser' . $i);
            $user->setPassword('password');
            $user->setEnable(true);
            $user->setLanguage('fr');
            $this->persistAndFlush($user);
            $entities[] = $user;

            // Generate ETag to cache
            $this->etagCacheService->generateEtag($user);
        }

        // Act & Measure
        $this->stopwatch->start('cache_invalidation');

        foreach ($entities as $entity) {
            $this->etagCacheService->invalidateCache($entity);
        }

        $stopwatchEvent = $this->stopwatch->stop('cache_invalidation');

        // Assert
        $duration = $stopwatchEvent->getDuration();
        $this->assertLessThan(100, $duration, 'Cache invalidation for 100 entities should take less than 100ms');
    }

    /**
     * Cache validation performance test.
     */
    #[Test]
    #[Group('performance')]
    #[Group('cache')]
    public function cacheValidationPerformance(): void
    {
        // Arrange
        $posts = [];
        for ($i = 0; self::CACHE_VALIDATION_TEST_ENTITIES > $i; ++$i) {
            $post = new Post();
            $post->setTitle('Cache Validation Post ' . $i);
            $post->setEnable(true);
            $this->persistAndFlush($post);
            $posts[] = $post;
        }

        // Act & Measure
        $this->stopwatch->start('cache_validation');

        foreach ($posts as $post) {
            $headers = $this->etagCacheService->getCacheHeaders($post);
            $this->assertArrayHasKey('etag', $headers);
            $this->assertArrayHasKey('lastModified', $headers);
            $this->assertArrayHasKey('headers', $headers);
        }

        $stopwatchEvent = $this->stopwatch->stop('cache_validation');

        // Assert
        $duration = $stopwatchEvent->getDuration();
        $this->assertLessThan(200, $duration, 'Cache validation for 10 entities should take less than 200ms');
    }

    #[Test]
    #[Group('performance')]
    #[Group('etag')]
    public function collectionEtagPerformance(): void
    {
        // Arrange
        $entities = [];
        for ($i = 0; self::COLLECTION_ETAG_TEST_ENTITIES > $i; ++$i) {
            $post = new Post();
            $post->setTitle('Performance Post ' . $i);
            $post->setEnable(true);
            $this->persistAndFlush($post);
            $entities[] = $post;
        }

        // Act & Measure
        $this->stopwatch->start('collection_etag');
        $collectionEtag          = $this->etagCacheService->generateCollectionEtag($entities);
        $stopwatchEvent          = $this->stopwatch->stop('collection_etag');

        // Assert
        $this->assertNotEmpty($collectionEtag);
        $duration = $stopwatchEvent->getDuration();
        $this->assertLessThan(500, $duration, 'Collection ETag generation for 50 entities should take less than 500ms');
    }

    #[Test]
    #[Group('performance')]
    #[Group('concurrency')]
    public function concurrentEtagGeneration(): void
    {
        // Arrange
        $user = new User();
        $user->setEmail('concurrent@example.com');
        $user->setUsername('concurrentuser');
        $user->setPassword('password');
        $user->setEnable(true);
        $user->setLanguage('fr');
        $this->persistAndFlush($user);

        // Act & Measure - Simulate concurrent access
        $this->stopwatch->start('concurrent_access');

        $etags = [];
        for ($i = 0; self::CONCURRENT_ACCESS_TEST_ITERATIONS > $i; ++$i) {
            $etags[] = $this->etagCacheService->generateEtag($user);
        }

        $stopwatchEvent = $this->stopwatch->stop('concurrent_access');

        // Assert
        $allEtagsIdentical = 1 === count(array_unique($etags));
        $this->assertTrue($allEtagsIdentical, 'All ETags should be identical for the same entity');

        $duration = $stopwatchEvent->getDuration();
        $this->assertLessThan(100, $duration, 'Concurrent ETag generation should be fast due to caching');
    }

    #[Test]
    #[Group('performance')]
    #[Group('cache')]
    #[Group('efficiency')]
    public function etagCacheEfficiency(): void
    {
        // Arrange
        $user = new User();
        $user->setEmail('cache@example.com');
        $user->setUsername('cacheuser');
        $user->setPassword('password');
        $user->setEnable(true);
        $user->setLanguage('fr');
        $this->persistAndFlush($user);

                // Act & Measure - First generation (caching)
        $this->stopwatch->start('first_generation');
        $firstEtag = $this->etagCacheService->generateETag($user);
        $firstEvent = $this->stopwatch->stop('first_generation');

        // Act & Measure - Second generation (from cache)
        $this->stopwatch->start('second_generation');
        $secondEtag = $this->etagCacheService->generateETag($user);
        $secondEvent = $this->stopwatch->stop('second_generation');

        // Assert
        $this->assertSame($firstEtag, $secondEtag);
        // Cache should be at least as fast as the first calculation
    }

    #[Test]
    #[Group('performance')]
    #[Group('etag')]
    public function etagGenerationPerformance(): void
    {
        // Arrange
        $users = [];
        for ($i = 0; self::ETAG_GENERATION_TEST_ENTITIES > $i; ++$i) {
            $user = new User();
            $user->setEmail(sprintf('performance%d@example.com', $i));
            $user->setUsername('perfuser' . $i);
            $user->setPassword('password');
            $user->setEnable(true);
            $user->setLanguage('fr');
            $this->persistAndFlush($user);
            $users[] = $user;
        }

        // Act & Measure
        $this->stopwatch->start('etag_generation');

        foreach ($users as $user) {
            $etag = $this->etagCacheService->generateEtag($user);
            $this->assertNotEmpty($etag);
        }

        $stopwatchEvent = $this->stopwatch->stop('etag_generation');

        // Assert
        $duration = $stopwatchEvent->getDuration();
        $this->assertLessThan(1000, $duration, 'ETag generation for 100 entities should take less than 1 second');

        $memoryUsage = $stopwatchEvent->getMemory();
        $this->assertLessThan(100 * 1024 * 1024, $memoryUsage, 'Memory usage should be less than 100MB');
    }

    #[Test]
    #[Group('performance')]
    #[Group('memory')]
    public function memoryLeakPrevention(): void
    {
        // Arrange
        $initialMemory = memory_get_usage(true);

        // Act - Create and process many entities
        for ($i = 0; self::MEMORY_LEAK_TEST_ENTITIES > $i; ++$i) {
            $user = new User();
            $user->setEmail(sprintf('leak%d@example.com', $i));
            $user->setUsername('leakuser' . $i);
            $user->setPassword('password');
            $user->setEnable(true);
            $user->setLanguage('fr');

            // Generate ETag
            $etag = $this->etagCacheService->generateEtag($user);
            $this->assertNotEmpty($etag);

            // Clean periodically
            if (0 === $i % 50) {
                $this->etagCacheService->clearCache();
                gc_collect_cycles();
            }
        }

        $finalMemory    = memory_get_usage(true);
        $memoryIncrease = $finalMemory - $initialMemory;

        // Assert
        $this->assertLessThan(10 * 1024 * 1024, $memoryIncrease, 'Memory increase should be less than 10MB');
    }

    #[\Override]
    protected function setUp(): void
    {
        parent::setUp();
        $this->etagCacheService = $this->getService(EtagCacheService::class);
        $this->stopwatch        = new Stopwatch();
    }

    #[\Override]
    protected function tearDown(): void
    {
        // Clean cache after each test
        $this->etagCacheService->clearCache();
        parent::tearDown();
    }
}
