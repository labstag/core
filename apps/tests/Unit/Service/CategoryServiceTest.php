<?php

declare(strict_types=1);

namespace Labstag\Tests\Unit\Service;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Labstag\Entity\Category;
use Labstag\Repository\CategoryRepository;
use Labstag\Service\CategoryService;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

final class CategoryServiceTest extends TestCase
{

    private EntityManagerInterface&MockObject $em;

    private CategoryRepository&MockObject $repository;

    private CategoryService $service;

    #[Test]
    #[Group('unit')]
    public function getCategoryMovieForFormEmpty(): void
    {
        $this->repository->method('findAllByTypeMovieEnable')->willReturn([]);
        $this->assertSame([], $this->service->getCategoryMovieForForm());
    }

    #[Test]
    #[Group('unit')]
    public function getTypeReturnsExisting(): void
    {
        $cat = $this->createMock(Category::class);
        $cat->method('getTitle')->willReturn('Foo');
        $repo = $this->createMock(EntityRepository::class);
        $repo->method('findAll')->willReturn([$cat]);
        $this->em->method('getRepository')->willReturn($repo);
        $this->assertSame($cat, $this->service->getType('Foo', Category::class));
    }

    protected function setUp(): void
    {
        $this->repository = $this->createMock(CategoryRepository::class);
        $this->em         = $this->createMock(EntityManagerInterface::class);
        $this->service    = new CategoryService($this->em, $this->repository);
    }
}
