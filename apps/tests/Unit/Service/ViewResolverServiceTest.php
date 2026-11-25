<?php

declare(strict_types=1);

namespace Labstag\Tests\Unit\Service;

use ReflectionClass;
use Doctrine\ORM\EntityManagerInterface;
use Labstag\Repository\BlockRepository;
use Labstag\Service\BlockService;
use Labstag\Service\ConfigurationService;
use Labstag\Service\ViewResolverService;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Twig\Environment;
use Twig\Loader\ArrayLoader;

final class ViewResolverServiceTest extends TestCase
{
    #[Test]
    #[Group('unit')]
    public function getViewByEntityNameReturnsDefaultWhenNoTemplate(): void
    {
        $twig    = new Environment(new ArrayLoader([]));
        $service = new ViewResolverService(
            [],
            $this->createStub(EntityManagerInterface::class),
            $this->createStub(ConfigurationService::class),
            $this->createStub(BlockService::class),
            $this->createStub(BlockRepository::class),
            $twig,
        );
        $ref    = new ReflectionClass($service);
        $method = $ref->getMethod('getViewByEntityName');
        $this->assertSame('views/default.html.twig', $method->invoke($service, 'Foo'));
    }
}
