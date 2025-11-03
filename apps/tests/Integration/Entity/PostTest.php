<?php

declare(strict_types=1);

namespace Labstag\Tests\Integration\Entity;

use DateTime;
use Labstag\Entity\Category;
use Labstag\Entity\Meta;
use Labstag\Entity\Post;
use Labstag\Entity\Tag;
use Labstag\Entity\User;
use Labstag\Tests\AbstractTestCase;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;

final class PostTest extends AbstractTestCase
{
    #[Test]
    #[Group('integration')]
    #[Group('entity')]
    #[Group('post')]
    #[Group('relationship')]
    public function postcategoriesrelationship(): void
    {
        // Arrange
        $category1 = new Category();
        $category1->setTitle('Technology');
        $category1->setType('post');

        $category2 = new Category();
        $category2->setTitle('Programming');
        $category2->setType('post');

        $post = new Post();
        $post->setTitle('Tech Post');
        $post->setEnable(true);
        $post->addCategory($category1);
        $post->addCategory($category2);

        // Act
        $this->persistAndFlush($category1);
        $this->persistAndFlush($category2);
        $this->persistAndFlush($post);

        // Assert
        $this->assertCount(2, $post->getCategories());
        $this->assertTrue($post->getCategories()->contains($category1));
        $this->assertTrue($post->getCategories()->contains($category2));
        $this->assertTrue($category1->getPosts()->contains($post));
        $this->assertTrue($category2->getPosts()->contains($post));
    }

    #[Test]
    #[Group('integration')]
    #[Group('entity')]
    #[Group('post')]
    public function postcreationwithrequiredfields(): void
    {
        // Arrange
        $post = new Post();
        $post->setTitle('Test Post Title');
        $post->setEnable(true);

        // Act
        $this->persistAndFlush($post);

        // Assert
        $this->assertNotNull($post->getId());
        $this->assertSame('Test Post Title', $post->getTitle());
        $this->assertTrue($post->isEnable());
        $this->assertNotNull($post->getSlug());
        // Gedmo should generate slug
    }

    #[Test]
    #[Group('integration')]
    #[Group('entity')]
    #[Group('post')]
    #[Group('state')]
    public function postenabledisable(): void
    {
        // Arrange
        $post = new Post();
        $post->setTitle('Enable/Disable Test');
        $post->setEnable(true);

        $this->persistAndFlush($post);

        // Act
        $post->setEnable(false);
        $this->persistAndFlush($post);

        // Assert
        $this->assertFalse($post->isEnable());
    }

    #[Test]
    #[Group('integration')]
    #[Group('entity')]
    #[Group('post')]
    #[Group('relationship')]
    #[Group('meta')]
    public function postmetarelationship(): void
    {
        // Arrange
        $meta = new Meta();
        $meta->setTitle('Meta Title');
        $meta->setDescription('Meta Description');

        $post = new Post();
        $post->setTitle('Post with Meta');
        $post->setEnable(true);

        // Establish bidirectional relationship via Meta
        $meta->setPost($post);

        // Act
        $this->persistAndFlush($post);

        // Assert
        $this->assertEquals($meta, $post->getMeta());
        $this->assertEquals($post, $meta->getPost());
    }

    #[Test]
    #[Group('integration')]
    public function postremovecategoryrelationship(): void
    {
        // Arrange
        $category = new Category();
        $category->setTitle('Removable Category');
        $category->setType('post');

        $post = new Post();
        $post->setTitle('Post with Removable Category');
        $post->setEnable(true);
        $post->addCategory($category);

        $this->persistAndFlush($category);
        $this->persistAndFlush($post);

        // Act
        $post->removeCategory($category);
        $this->persistAndFlush($post);

        // Assert
        $this->assertCount(0, $post->getCategories());
        $this->assertFalse($category->getPosts()->contains($post));
    }

    #[Test]
    #[Group('integration')]
    public function postremovetagrelationship(): void
    {
        // Arrange
        $tag = new Tag();
        $tag->setTitle('Removable Tag');
        $tag->setType('post');

        $post = new Post();
        $post->setTitle('Post with Removable Tag');
        $post->setEnable(true);
        $post->addTag($tag);

        $this->persistAndFlush($tag);
        $this->persistAndFlush($post);

        // Act
        $post->removeTag($tag);
        $this->persistAndFlush($post);

        // Assert
        $this->assertCount(0, $post->getTags());
        $this->assertFalse($tag->getPosts()->contains($post));
    }

    #[Test]
    #[Group('integration')]
    public function postsluggeneration(): void
    {
        // Arrange
        $post = new Post();
        $post->setTitle('This is a Test Post for Slug Generation');
        $post->setEnable(true);

        // Act
        $this->persistAndFlush($post);

        // Assert
        $this->assertNotNull($post->getSlug());
        $this->assertStringContainsString('this-is-a-test-post', $post->getSlug());
    }

    #[Test]
    #[Group('integration')]
    public function postsluguniqueness(): void
    {
        // Arrange
        $post1 = new Post();
        $post1->setTitle('Unique Title');
        $post1->setEnable(true);

        $post2 = new Post();
        $post2->setTitle('Unique Title');
        // Same title should create different slug
        $post2->setEnable(true);

        // Act
        $this->persistAndFlush($post1);
        $this->persistAndFlush($post2);

        // Assert
        $this->assertNotEquals($post1->getSlug(), $post2->getSlug());
    }

    #[Test]
    #[Group('integration')]
    public function poststringrepresentation(): void
    {
        // Arrange
        $post = new Post();
        $post->setTitle('String Representation Test');
        $post->setEnable(true);

        // Act
        $stringRepresentation = (string) $post;

        // Assert
        $this->assertSame('String Representation Test', $stringRepresentation);
    }

    #[Test]
    #[Group('integration')]
    public function posttagsrelationship(): void
    {
        // Arrange
        $tag1 = new Tag();
        $tag1->setTitle('PHP');
        $tag1->setType('post');

        $tag2 = new Tag();
        $tag2->setTitle('Symfony');
        $tag2->setType('post');

        $post = new Post();
        $post->setTitle('PHP Symfony Post');
        $post->setEnable(true);
        $post->addTag($tag1);
        $post->addTag($tag2);

        // Act
        $this->persistAndFlush($tag1);
        $this->persistAndFlush($tag2);
        $this->persistAndFlush($post);

        // Assert
        $this->assertCount(2, $post->getTags());
        $this->assertTrue($post->getTags()->contains($tag1));
        $this->assertTrue($post->getTags()->contains($tag2));
        $this->assertTrue($tag1->getPosts()->contains($post));
        $this->assertTrue($tag2->getPosts()->contains($post));
    }

    #[Test]
    #[Group('integration')]
    public function posttimestampabletraitcreation(): void
    {
        // Arrange
        $post = new Post();
        $post->setTitle('Timestamp Test Post');
        $post->setEnable(true);

        // Act
        $this->persistAndFlush($post);

        // Assert
        $this->assertInstanceOf(DateTime::class, $post->getCreatedAt());
        $this->assertInstanceOf(DateTime::class, $post->getUpdatedAt());
        $this->assertInstanceOf(DateTime::class, $post->getCreatedAt());
        $this->assertInstanceOf(DateTime::class, $post->getUpdatedAt());
    }

    #[Test]
    #[Group('integration')]
    public function postuserrelationship(): void
    {
        // Arrange
        $user = new User();
        $user->setEmail('postauthor@example.com');
        $user->setUsername('postauthor');
        $user->setPassword('password');
        $user->setEnable(true);
        $user->setLanguage('fr');

        $post = new Post();
        $post->setTitle('Post by User');
        $post->setRefuser($user);
        $post->setEnable(true);

        // Act
        $this->persistAndFlush($user);
        $this->persistAndFlush($post);

        $this->refresh($user);

        // Assert
        $this->assertEquals($user, $post->getRefuser());
        $this->assertTrue($user->getPosts()->contains($post));
    }

    #[Test]
    #[Group('integration')]
    public function postwithresume(): void
    {
        // Arrange
        $resume = 'This is a test resume for the post content.';
        $post   = new Post();
        $post->setTitle('Post with Resume');
        $post->setResume($resume);
        $post->setEnable(true);

        // Act
        $this->persistAndFlush($post);

        // Assert
        $this->assertSame($resume, $post->getResume());
    }
}
