<?php

declare(strict_types=1);

namespace Labstag\Tests\Integration\Entity;

use DateTime;
use DateTimeImmutable;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Labstag\Entity\Post;
use Labstag\Entity\User;
use Labstag\Tests\AbstractTestCase;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use ReflectionProperty;

final class UserTest extends AbstractTestCase
{
    #[Test]
    #[Group('integration')]
    #[Group('entity')]
    #[Group('user')]
    public function userCreationWithRequiredFields(): void
    {
        // Arrange
        $user = new User();
        $user->setEmail('test@example.com');
        $user->setUsername('testuser');
        $user->setPassword('hashed_password');
        $user->setEnable(true);
        $user->setLanguage('fr');

        // Act
        $this->persistAndFlush($user);

        // Assert
        $this->assertNotNull($user->getId());
        $this->assertSame('test@example.com', $user->getEmail());
        $this->assertSame('testuser', $user->getUsername());
        $this->assertTrue($user->isEnable());
        $this->assertSame('fr', $user->getLanguage());
        $this->assertContains('ROLE_USER', $user->getRoles());
    }

    #[Test]
    #[Group('integration')]
    #[Group('entity')]
    #[Group('user')]
    #[Group('validation')]
    public function userEmailUniqueness(): void
    {
        // Arrange
        $user1 = new User();
        $user1->setEmail('unique@example.com');
        $user1->setUsername('user1');
        $user1->setPassword('password');
        $user1->setEnable(true);
        $user1->setLanguage('fr');

        $user2 = new User();
        $user2->setEmail('unique@example.com');
        // Same email
        $user2->setUsername('user2');
        $user2->setPassword('password');
        $user2->setEnable(true);
        $user2->setLanguage('fr');

        // Act & Assert
        $this->persistAndFlush($user1);

        $this->expectException(UniqueConstraintViolationException::class);
        $this->persistAndFlush($user2);
    }

    #[Test]
    #[Group('integration')]
    #[Group('entity')]
    #[Group('user')]
    #[Group('security')]
    public function usergetuseridentifier(): void
    {
        // Arrange
        $user = new User();
        $user->setEmail('identifier@example.com');
        $user->setUsername('identifieruser');
        $user->setPassword('password');
        $user->setEnable(true);
        $user->setLanguage('fr');

        // Act
        $identifier = $user->getUserIdentifier();

        // Assert
        $this->assertSame('identifier@example.com', $identifier);
    }

    #[Test]
    #[Group('integration')]
    #[Group('entity')]
    #[Group('user')]
    #[Group('security')]
    public function userpasswordencryption(): void
    {
        // Arrange
        $plainPassword  = 'my_secret_password';
        $hashedPassword = password_hash($plainPassword, PASSWORD_DEFAULT);

        $user = new User();
        $user->setEmail('password@example.com');
        $user->setUsername('passworduser');
        $user->setPassword($hashedPassword);
        $user->setEnable(true);
        $user->setLanguage('fr');

        // Act
        $this->persistAndFlush($user);

        // Assert
        $this->assertNotSame($plainPassword, $user->getPassword());
        $this->assertTrue(password_verify($plainPassword, (string) $user->getPassword()));
    }

    #[Test]
    #[Group('integration')]
    #[Group('entity')]
    #[Group('user')]
    #[Group('relationship')]
    public function userpostsrelationship(): void
    {
        // Arrange
        $user = new User();
        $user->setEmail('author@example.com');
        $user->setUsername('author');
        $user->setPassword('password');
        $user->setEnable(true);
        $user->setLanguage('fr');

        $post1 = new Post();
        $post1->setTitle('First Post');
        $post1->setRefuser($user);
        $post1->setEnable(true);

        $post2 = new Post();
        $post2->setTitle('Second Post');
        $post2->setRefuser($user);
        $post2->setEnable(true);

        // Act
        $this->persistAndFlush($user);
        $this->persistAndFlush($post1);
        $this->persistAndFlush($post2);

        $this->refresh($user);

        // Assert
        $this->assertCount(2, $user->getPosts());
        $this->assertTrue($user->getPosts()->contains($post1));
        $this->assertTrue($user->getPosts()->contains($post2));
    }

    #[Test]
    #[Group('integration')]
    #[Group('entity')]
    #[Group('user')]
    #[Group('security')]
    public function userrolescustomroles(): void
    {
        // Arrange
        $user = new User();
        $user->setEmail('admin@example.com');
        $user->setUsername('admin');
        $user->setPassword('password');
        $user->setRoles(['ROLE_ADMIN', 'ROLE_EDITOR']);
        $user->setEnable(true);
        $user->setLanguage('fr');

        // Act & Assert
        $roles = $user->getRoles();

        $this->assertContains('ROLE_USER', $roles);
        $this->assertContains('ROLE_ADMIN', $roles);
        $this->assertContains('ROLE_EDITOR', $roles);
    }

    #[Test]
    #[Group('integration')]
    public function userrolesdefaultbehavior(): void
    {
        // Arrange
        $user = new User();
        $user->setEmail('roles@example.com');
        $user->setUsername('rolesuser');
        $user->setPassword('password');
        $user->setEnable(true);
        $user->setLanguage('fr');

        // Act & Assert
        $roles = $user->getRoles();

        $this->assertContains('ROLE_USER', $roles);
        $this->assertCount(1, array_unique($roles));
        // No duplicates
    }

    #[Test]
    #[Group('integration')]
    public function usersoftdeletion(): void
    {
        // Arrange
        $user = new User();
        $user->setEmail('softdelete@example.com');
        $user->setUsername('softdeleteuser');
        $user->setPassword('password');
        $user->setEnable(true);
        $user->setLanguage('fr');

        $this->persistAndFlush($user);
        $userId = $user->getId();

        // Act - Simulate soft deletion by setting deletedAt
        $reflectionProperty = new ReflectionProperty(User::class, 'deletedAt');
        $reflectionProperty->setValue($user, DateTime::createFromImmutable(new DateTimeImmutable()));

        $this->persistAndFlush($user);

        // Clear entity manager to force fresh query
        $this->entityManager->clear();

        // Assert
        $deletedUser = $this->entityManager->getRepository(User::class)->find($userId);
        $this->assertNotInstanceOf(User::class, $deletedUser);
        // Should not be found due to soft delete filter
    }

    #[Test]
    #[Group('integration')]
    public function userstringrepresentation(): void
    {
        // Arrange
        $user = new User();
        $user->setUsername('stringtest');
        $user->setEmail('string@example.com');
        $user->setPassword('password');
        $user->setEnable(true);
        $user->setLanguage('fr');

        // Act
        $stringRepresentation = (string) $user;

        // Assert
        $this->assertSame('stringtest', $stringRepresentation);
    }

    #[Test]
    #[Group('integration')]
    public function usertimestampabletraitcreation(): void
    {
        // Arrange
        $user = new User();
        $user->setEmail('timestamp@example.com');
        $user->setUsername('timestamp_user');
        $user->setPassword('password');
        $user->setEnable(true);
        $user->setLanguage('fr');

        // Act
        $this->persistAndFlush($user);

        // Assert
        $this->assertInstanceOf(DateTime::class, $user->getCreatedAt());
        $this->assertInstanceOf(DateTime::class, $user->getUpdatedAt());
        $this->assertInstanceOf(DateTime::class, $user->getCreatedAt());
        $this->assertInstanceOf(DateTime::class, $user->getUpdatedAt());
    }

    #[Test]
    #[Group('integration')]
    public function userusernameuniqueness(): void
    {
        // Arrange
        $user1 = new User();
        $user1->setEmail('email1@example.com');
        $user1->setUsername('uniqueusername');
        $user1->setPassword('password');
        $user1->setEnable(true);
        $user1->setLanguage('fr');

        $user2 = new User();
        $user2->setEmail('email2@example.com');
        $user2->setUsername('uniqueusername');
        // Same username
        $user2->setPassword('password');
        $user2->setEnable(true);
        $user2->setLanguage('fr');

        // Act & Assert
        $this->persistAndFlush($user1);

        $this->expectException(UniqueConstraintViolationException::class);
        $this->persistAndFlush($user2);
    }
}
