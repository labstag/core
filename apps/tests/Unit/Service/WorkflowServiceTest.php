<?php

declare(strict_types=1);

namespace Labstag\Tests\Unit\Service;

use Doctrine\ORM\EntityManagerInterface;
use Labstag\Service\WorkflowService;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use stdClass;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Workflow\Registry;

final class WorkflowServiceTest extends TestCase
{
    #[Test]
    #[Group('unit')]
    public function changeDoesNothingWhenNoWorkflow(): void
    {
        $registry = $this->createMock(Registry::class);
        $registry->method('has')->willReturn(false);
        $em      = $this->createMock(EntityManagerInterface::class);
        $service = new WorkflowService(
            $registry,
            new RequestStack(),
            $em,
        );
        $em->expects($this->never())->method('flush');
        $service->change('Foo', 't', 1);
        $this->assertTrue(true);
    }

    #[Test]
    #[Group('unit')]
    public function getPlacesReturnsEmptyWhenNoWorkflow(): void
    {
        $registry = $this->createMock(Registry::class);
        $registry->method('has')->willReturn(false);
        $service = $this->makeService($registry);
        $this->assertSame([], $service->getPlaces(new stdClass()));
    }

    #[Test]
    #[Group('unit')]
    public function getReturnsNullWhenRegistryHasNot(): void
    {
        $registry = $this->createMock(Registry::class);
        $registry->method('has')->willReturn(false);
        $service = $this->makeService($registry);
        $this->assertNull($service->get(new stdClass()));
    }

    #[Test]
    #[Group('unit')]
    public function hasReturnsFalseWhenRegistryHasNot(): void
    {
        $registry = $this->createMock(Registry::class);
        $registry->method('has')->willReturn(false);
        $service = $this->makeService($registry);
        $this->assertFalse($service->has(new stdClass()));
    }

    private function makeService(?Registry $registry = null): WorkflowService
    {
        return new WorkflowService(
            $registry ?? $this->createMock(Registry::class),
            new RequestStack(),
            $this->createStub(EntityManagerInterface::class),
        );
    }
}
