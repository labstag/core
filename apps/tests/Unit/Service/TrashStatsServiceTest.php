<?php

declare(strict_types=1);

namespace Labstag\Tests\Unit\Service;

use Labstag\Service\TrashStatsService;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class TrashStatsServiceTest extends TestCase
{

    #[Test]
    #[Group('unit')]
    public function emptyControllersReturnsEmpty(): void
    {
        $service = new TrashStatsService();
        $this->assertSame([], $service->getTrashItems([], $this->createMock(\Doctrine\ORM\EntityManagerInterface::class), $this->createMock(\EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGeneratorInterface::class)));
    }
}
