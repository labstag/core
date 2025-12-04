<?php

declare(strict_types=1);

namespace Labstag\Tests\Unit\Service;

use DateTime;
use DateTimeImmutable;
use DateTimeInterface;
use Labstag\Entity\User;
use Labstag\Service\EtagCacheService;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use ReflectionProperty;

final class EtagCacheServiceTest extends TestCase
{
    private EtagCacheService $etagCacheService;

    #[Test]
    #[Group('unit')]
    #[Group('etag')]
    public function generateCollectionEtagWithEmptyArray(): void
    {
        // Act
        $etag = $this->etagCacheService->generateCollectionEtag([]);

        // Assert
        $this->assertSame(sha1('empty-collection'), $etag);
    }

    #[Test]
    #[Group('unit')]
    #[Group('etag')]
    public function generateCollectionEtagWithEntities(): void
    {
        // Arrange
        $user1    = $this->createMockUser('123e4567-e89b-12d3-a456-426614174000', 'test1@example.com');
        $user2    = $this->createMockUser('123e4567-e89b-12d3-a456-426614174001', 'test2@example.com');
        $entities = [$user1, $user2];

        // Act
        $etag = $this->etagCacheService->generateCollectionEtag($entities);

        // Assert
        $this->assertIsString($etag);
        $this->assertNotEmpty($etag);
        $this->assertSame(40, strlen($etag));
        // SHA1 length
    }

    #[Test]
    #[Group('unit')]
    #[Group('etag')]
    public function generateEtagChangesWhenEntityChanges(): void
    {
        // Arrange
        $user1 = $this->createMockUser('123e4567-e89b-12d3-a456-426614174000', 'test@example.com');
        $user2 = $this->createMockUser('123e4567-e89b-12d3-a456-426614174001', 'test2@example.com');

        // Act
        $etag1 = $this->etagCacheService->generateEtag($user1);
        $etag2 = $this->etagCacheService->generateEtag($user2);

        // Assert
        $this->assertNotSame($etag1, $etag2);
    }

    #[Test]
    #[Group('unit')]
    #[Group('etag')]
    public function generateEtagForEntityWithId(): void
    {
        // Arrange
        $user = $this->createMockUser('123e4567-e89b-12d3-a456-426614174000', 'test@example.com');

        // Act
        $etag = $this->etagCacheService->generateEtag($user);

        // Assert
        $this->assertIsString($etag);
        $this->assertNotEmpty($etag);
        $this->assertSame(40, strlen($etag));
        // SHA1 length
    }

    #[Test]
    #[Group('unit')]
    #[Group('etag')]
    public function generateEtagIsConsistent(): void
    {
        // Arrange
        $user = $this->createMockUser('123e4567-e89b-12d3-a456-426614174000', 'test@example.com');

        // Act
        $etag1 = $this->etagCacheService->generateEtag($user);
        $etag2 = $this->etagCacheService->generateEtag($user);

        // Assert
        $this->assertSame($etag1, $etag2);
    }

    #[Test]
    #[Group('unit')]
    #[Group('cache')]
    public function getCacheHeaders(): void
    {
        // Arrange
        $user = $this->createMockUser('123', 'test@example.com');

        // Act
        $headers = $this->etagCacheService->getCacheHeaders($user);

        // Assert
        $this->assertArrayHasKey('etag', $headers);
        $this->assertArrayHasKey('lastModified', $headers);
        $this->assertArrayHasKey('headers', $headers);
        $this->assertArrayHasKey('Cache-Control', $headers['headers']);
        $this->assertArrayHasKey('ETag', $headers['headers']);
    }

    #[Test]
    #[Group('unit')]
    #[Group('lastmodified')]
    public function getLastModifiedWithCreatedAtOnly(): void
    {
        // Arrange
        $createdAt = new DateTimeImmutable('2025-01-01 10:00:00');
        $user      = $this->createMockUser('123', 'test@example.com', $createdAt);

        // Act
        $lastModified = $this->etagCacheService->getLastModified($user);

        // Assert
        $this->assertEquals($createdAt, $lastModified);
    }

    #[Test]
    #[Group('unit')]
    #[Group('lastmodified')]
    public function getLastModifiedWithoutDates(): void
    {
        // Arrange
        $user = $this->createMockUser('123', 'test@example.com');

        // Act
        $lastModified = $this->etagCacheService->getLastModified($user);

        // Assert
        $this->assertNotInstanceOf(DateTimeInterface::class, $lastModified);
    }

    #[Test]
    #[Group('unit')]
    #[Group('lastmodified')]
    public function getLastModifiedWithUpdatedAt(): void
    {
        // Arrange
        $updatedAt = new DateTimeImmutable('2025-01-01 12:00:00');
        $user      = $this->createMockUser('123', 'test@example.com', null, $updatedAt);

        // Act
        $lastModified = $this->etagCacheService->getLastModified($user);

        // Assert
        $this->assertEquals($updatedAt, $lastModified);
    }

    #[Test]
    #[Group('unit')]
    #[Group('cache')]
    public function invalidateCache(): void
    {
        // Arrange
        $user = $this->createMockUser('123', 'test@example.com');

        // Generate an ETag to cache it
        $etagBefore = $this->etagCacheService->generateETag($user);

        // Act
        $this->etagCacheService->clearCache();

        // Internal cache should be cleared, but ETag should be the same
        // because it is based on entity properties
        $etagAfter = $this->etagCacheService->generateETag($user);

        // Assert
        $this->assertSame($etagBefore, $etagAfter);
    }

    #[Test]
    #[Group('unit')]
    #[Group('lastmodified')]
    public function isModifiedSinceDateWithNewerDate(): void
    {
        // Arrange
        $lastModified = new DateTimeImmutable('2025-01-01 12:00:00');
        $user         = $this->createMockUser('123', 'test@example.com', null, $lastModified);
        $olderDate    = new DateTimeImmutable('2025-01-01 10:00:00');

        // Act
        $isModified = $this->etagCacheService->isModifiedSinceDate($user, $olderDate);

        // Assert
        $this->assertTrue($isModified);
    }

    #[Test]
    #[Group('unit')]
    #[Group('lastmodified')]
    public function isModifiedSinceDateWithOlderDate(): void
    {
        // Arrange
        $lastModified = new DateTimeImmutable('2025-01-01 10:00:00');
        $user         = $this->createMockUser('123', 'test@example.com', null, $lastModified);
        $newerDate    = new DateTimeImmutable('2025-01-01 12:00:00');

        // Act
        $isModified = $this->etagCacheService->isModifiedSinceDate($user, $newerDate);

        // Assert
        $this->assertFalse($isModified);
    }

    #[Test]
    #[Group('unit')]
    #[Group('etag')]
    public function isModifiedSinceWithDifferentEtags(): void
    {
        // Arrange
        $user          = $this->createMockUser('123', 'test@example.com');
        $this->etagCacheService->generateEtag($user);
        $differentEtag = 'different-etag';

        // Act
        $isModified = $this->etagCacheService->isModifiedSince($user, $differentEtag);

        // Assert
        $this->assertTrue($isModified);
    }

    #[Test]
    #[Group('unit')]
    #[Group('etag')]
    public function isModifiedSinceWithSameEtag(): void
    {
        // Arrange
        $user        = $this->createMockUser('123', 'test@example.com');
        $currentEtag = $this->etagCacheService->generateEtag($user);

        // Act
        $isModified = $this->etagCacheService->isModifiedSince($user, $currentEtag);

        // Assert
        $this->assertFalse($isModified);
    }

    protected function setUp(): void
    {
        $this->etagCacheService = new EtagCacheService();
    }

    protected function tearDown(): void
    {
        // Clean internal cache after each test
        $this->etagCacheService->clearCache();
    }

    private function createMockUser(
        string $id,
        string $email,
        ?DateTimeImmutable $createdAt = null,
        ?DateTimeImmutable $updatedAt = null,
    ): User
    {
        $user = new User();

        // Use reflection to set ID (protected property)
        $idProperty = new ReflectionProperty(User::class, 'id');
        $idProperty->setValue($user, $id);

        $user->setEmail($email);
        $user->setUsername('test_user_' . substr($id, 0, 8));

        if ($createdAt instanceof DateTimeImmutable) {
            $createdAtProperty = new ReflectionProperty(User::class, 'createdAt');
            $createdAtProperty->setValue($user, DateTime::createFromImmutable($createdAt));
        }

        if ($updatedAt instanceof DateTimeImmutable) {
            $updatedAtProperty = new ReflectionProperty(User::class, 'updatedAt');
            $updatedAtProperty->setValue($user, DateTime::createFromImmutable($updatedAt));
        }

        return $user;
    }
}
