<?php

declare(strict_types=1);

namespace Labstag\Tests\Fixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;
use Labstag\Entity\Post;
use Labstag\Entity\User;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

/**
 * Lightweight fixtures for performance tests.
 */
final class PerformanceFixtures extends Fixture
{
    private const FLUSH_BATCH_SIZE        = 50;

    private const PERFORMANCE_USERS_COUNT = 100;

    private const POSTS_PER_USER_BATCH    = 5;

    private const USER_BATCH_SIZE         = 20;

    public function __construct(
        private UserPasswordHasherInterface $userPasswordHasher,
    )
    {
    }

    public function load(ObjectManager $manager): void
    {
        $generator = Factory::create('fr_FR');

        // Create 100 users for performance tests
        for ($i = 1; self::PERFORMANCE_USERS_COUNT >= $i; ++$i) {
            $user = new User();
            $user->setEmail(sprintf('perf.user.%d@labstag.test', $i));
            $user->setUsername('perfuser' . $i);
            $user->setEnable(true);
            $user->setLanguage('fr');

            $hashedPassword = $this->userPasswordHasher->hashPassword($user, 'password123');
            $user->setPassword($hashedPassword);

            $manager->persist($user);

            // Create 5 posts per user (every 20 users)
            if (0 === $i % self::USER_BATCH_SIZE) {
                for ($j = 1; self::POSTS_PER_USER_BATCH >= $j; ++$j) {
                    $post = new Post();
                    $post->setTitle($generator->sentence(6));
                    $post->setResume($generator->paragraph(3));
                    $post->setEnable(true);
                    $post->setRefuser($user);

                    $manager->persist($post);
                }
            }

            // Flush in batches to avoid memory issues
            if (0 === $i % self::FLUSH_BATCH_SIZE) {
                $manager->flush();
                $manager->clear();
            }
        }

        $manager->flush();
    }
}
