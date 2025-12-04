<?php

declare(strict_types=1);

namespace Labstag\Tests;

use Doctrine\ORM\EntityManagerInterface;
use Override;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

abstract class AbstractWebTestCase extends WebTestCase
{
    protected static ?KernelBrowser $client = null;

    /**
     * Static client reset to avoid conflicts between test classes.
     */
    #[Override]
    public static function tearDownAfterClass(): void
    {
        self::$client = null;
        parent::tearDownAfterClass();
    }

    /**
     * Get the reusable client.
     */
    protected function client(): KernelBrowser
    {
        if (!self::$client instanceof KernelBrowser) {
            self::$client = static::createClient();
        }

        return self::$client;
    }

    /**
     * Shortcut to get a service from the container.
     */
    protected function getService(string $serviceId): object
    {
        return static::getContainer()->get($serviceId);
    }

    /**
     * Persist and flush an entity for tests.
     */
    protected function persistAndFlush(object $entity): void
    {
        $entityManager = static::getContainer()->get(EntityManagerInterface::class);
        $entityManager->persist($entity);
        $entityManager->flush();
    }

    /**
     * Reload an entity from the database.
     */
    protected function refresh(object $entity): void
    {
        $entityManager = static::getContainer()->get(EntityManagerInterface::class);
        $entityManager->refresh($entity);
    }

    protected function setUp(): void
    {
        parent::setUp();

        // Create client only once per test class
        if (!self::$client instanceof KernelBrowser) {
            self::$client = static::createClient();
        }
    }

    #[Override]
    protected function tearDown(): void
    {
        parent::tearDown();

        // Clean database if available
        if (self::$client instanceof KernelBrowser) {
            $entityManager = self::getContainer()->get('doctrine')->getManager();
            // Clear all entities for following tests
            $entityManager->clear();
        }
    }
}
