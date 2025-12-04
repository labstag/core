<?php

declare(strict_types=1);

namespace Labstag\Tests;

use Doctrine\ORM\EntityManagerInterface;
use Override;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpKernel\KernelInterface;

abstract class AbstractTestCase extends KernelTestCase
{

    protected ContainerInterface $container;

    protected EntityManagerInterface $entityManager;

    /**
     * Shortcut to get a service from the container.
     */
    protected function getService(string $serviceId): object
    {
        return $this->container->get($serviceId);
    }

    /**
     * Persist and flush an entity for tests.
     */
    protected function persistAndFlush(object $entity): void
    {
        $this->entityManager->persist($entity);
        $this->entityManager->flush();
    }

    /**
     * Reload an entity from the database.
     */
    protected function refresh(object $entity): void
    {
        $this->entityManager->refresh($entity);
    }

    protected function setUp(): void
    {
        parent::setUp();

        // Check if kernel is not already started
        if (!self::$kernel instanceof KernelInterface) {
            self::bootKernel();
        }

        $this->container     = static::getContainer();
        $this->entityManager = $this->container->get(EntityManagerInterface::class);
    }

    #[Override]
    protected function tearDown(): void
    {
        parent::tearDown();

        // Clean EntityManager to avoid memory leaks
        $this->entityManager->clear();
        $this->entityManager->close();

        // Properly close the kernel
        if (self::$kernel instanceof KernelInterface) {
            self::$kernel->shutdown();
            self::$kernel = null;
        }
    }
}
