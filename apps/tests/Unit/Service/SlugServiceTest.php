<?php

declare(strict_types=1);

namespace Labstag\Tests\Unit\Service;

use stdClass;
use Labstag\Repository\PageRepository;
use InvalidArgumentException;
use Labstag\Data\DataInterface;
use Labstag\Service\SlugService;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\RequestStack;

final class SlugServiceTest extends TestCase
{

    private RequestStack $requestStack;

    private SlugService $slugService;

    #[Test]
    #[Group('unit')]
    public function forEntityThrowsOnUnsupported(): void
    {
        $entity = new stdClass();
        $this->expectException(InvalidArgumentException::class);
        $this->slugService->forEntity($entity);
    }

    #[Test]
    #[Group('unit')]
    public function forEntityUsesMatchingData(): void
    {
        $data = $this->createMock(DataInterface::class);
        $data->method('supportsData')->willReturn(true);
        $data->method('generateSlug')->willReturn(
            ['slug' => 'foo']
        );
        $service = new SlugService([$data], $this->createStub(PageRepository::class), $this->requestStack);
        $this->assertSame(
            ['slug' => 'foo'],
            $service->forEntity(new stdClass())
        );
    }

    #[Test]
    #[Group('unit')]
    public function getEntityBySlugNoMatch(): void
    {
        $this->assertNull($this->slugService->getEntityBySlug('nope'));
    }

    #[Test]
    #[Group('unit')]
    public function getEntityBySlugNullWhenNoMatch(): void
    {
        $this->assertNull($this->slugService->getEntityBySlug(null));
    }

    protected function setUp(): void
    {
        $this->requestStack = new RequestStack();
        $this->slugService  = new SlugService([], $this->createStub(PageRepository::class), $this->requestStack);
    }
}
