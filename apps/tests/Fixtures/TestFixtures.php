<?php

declare(strict_types=1);

namespace Labstag\Tests\Fixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;
use Labstag\Entity\Category;
use Labstag\Entity\Meta;
use Labstag\Entity\Post;
use Labstag\Entity\Tag;
use Labstag\Entity\User;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

final class TestFixtures extends Fixture
{
    public const CATEGORY_NEWS  = 'category_news';

    public const CATEGORY_TECH  = 'category_tech';

    public const POST_DRAFT     = 'post_draft';

    public const POST_PUBLISHED = 'post_published';

    public const TAG_PHP        = 'tag_php';

    public const TAG_SYMFONY    = 'tag_symfony';

    public const USER_ADMIN     = 'user_admin';

    public const USER_AUTHOR    = 'user_author';

    public const USER_READER    = 'user_reader';

    public function __construct(
        private UserPasswordHasherInterface $userPasswordHasher,
    )
    {
    }

    public function load(ObjectManager $manager): void
    {
        Factory::create('fr_FR');

        // Create users
        $user   = $this->createAdminUser();
        $author = $this->createAuthorUser();
        $reader = $this->createReaderUser();

        $manager->persist($user);
        $manager->persist($author);
        $manager->persist($reader);

        // Create categories
        $techCategory = $this->createTechCategory();
        $newsCategory = $this->createNewsCategory();

        $manager->persist($techCategory);
        $manager->persist($newsCategory);

        // Create tags
        $phpTag     = $this->createPhpTag();
        $symfonyTag = $this->createSymfonyTag();

        $manager->persist($phpTag);
        $manager->persist($symfonyTag);

        // Create posts
        $publishedPost = $this->createPublishedPost($author, $techCategory, [$phpTag, $symfonyTag]);
        $draftPost     = $this->createDraftPost($author, $newsCategory, [$phpTag]);

        $manager->persist($publishedPost);
        $manager->persist($draftPost);

        // Add references for tests
        $this->addReference(self::USER_ADMIN, $user);
        $this->addReference(self::USER_AUTHOR, $author);
        $this->addReference(self::USER_READER, $reader);
        $this->addReference(self::CATEGORY_TECH, $techCategory);
        $this->addReference(self::CATEGORY_NEWS, $newsCategory);
        $this->addReference(self::TAG_PHP, $phpTag);
        $this->addReference(self::TAG_SYMFONY, $symfonyTag);
        $this->addReference(self::POST_PUBLISHED, $publishedPost);
        $this->addReference(self::POST_DRAFT, $draftPost);

        $manager->flush();
    }

    private function createAdminUser(): User
    {
        $user = new User();
        $user->setEmail('admin@labstag.test');
        $user->setUsername('admin');
        $user->setRoles(['ROLE_ADMIN', 'ROLE_USER']);
        $user->setEnable(true);
        $user->setLanguage('fr');

        $hashedPassword = $this->userPasswordHasher->hashPassword($user, 'admin123');
        $user->setPassword($hashedPassword);

        return $user;
    }

    private function createAuthorUser(): User
    {
        $user = new User();
        $user->setEmail('author@labstag.test');
        $user->setUsername('author');
        $user->setRoles(['ROLE_AUTHOR', 'ROLE_USER']);
        $user->setEnable(true);
        $user->setLanguage('fr');

        $hashedPassword = $this->userPasswordHasher->hashPassword($user, 'author123');
        $user->setPassword($hashedPassword);

        return $user;
    }

    private function createDraftPost(User $user, Category $category, array $tags): Post
    {
        $post = new Post();
        $post->setTitle('Les Nouveautés PHP 8.4');
        $post->setSlug('nouveautes-php-84');
        $post->setResume('Découvrez les nouvelles fonctionnalités de PHP 8.4 en avant-première.');
        $post->setEnable(false);
        // Brouillon
        $post->setRefuser($user);
        $post->addCategory($category);

        foreach ($tags as $tag) {
            $post->addTag($tag);
        }

        return $post;
    }

    private function createNewsCategory(): Category
    {
        $category = new Category();
        $category->setTitle('Actualités');
        $category->setType('post');

        return $category;
    }

    private function createPhpTag(): Tag
    {
        $tag = new Tag();
        $tag->setTitle('PHP');

        return $tag;
    }

    private function createPublishedPost(User $user, Category $category, array $tags): Post
    {
        $post = new Post();
        $post->setTitle('Guide Complet Symfony 7.3');
        $post->setSlug('guide-complet-symfony-73');
        $post->setResume('Un guide complet pour débuter avec Symfony 7.3 et ses nouvelles fonctionnalités.');
        $post->setEnable(true);
        $post->setRefuser($user);
        $post->addCategory($category);

        foreach ($tags as $tag) {
            $post->addTag($tag);
        }

        // Add metadata
        $meta = new Meta();
        $meta->setTitle('Guide Symfony 7.3 - Tutoriel Complet');
        $meta->setDescription(
            'Apprenez Symfony 7.3 avec ce guide complet incluant les meilleures pratiques et les nouvelles fonctionnalités.'
        );
        $post->setMeta($meta);

        return $post;
    }

    private function createReaderUser(): User
    {
        $user = new User();
        $user->setEmail('reader@labstag.test');
        $user->setUsername('reader');
        $user->setRoles(['ROLE_USER']);
        $user->setEnable(true);
        $user->setLanguage('fr');

        $hashedPassword = $this->userPasswordHasher->hashPassword($user, 'reader123');
        $user->setPassword($hashedPassword);

        return $user;
    }

    private function createSymfonyTag(): Tag
    {
        $tag = new Tag();
        $tag->setTitle('Symfony');

        return $tag;
    }

    private function createTechCategory(): Category
    {
        $category = new Category();
        $category->setTitle('Technologie');
        $category->setType('post');

        return $category;
    }
}
