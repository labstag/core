<?php

declare(strict_types=1);

namespace Labstag\Tests\Unit\Service;

use Labstag\Service\GeocodeService;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class GeocodeServiceTest extends TestCase
{

    #[Test]
    #[Group('unit')]
    public function tablesParsesLines(): void
    {
        $service = new GeocodeService($this->createMock(\Symfony\Contracts\HttpClient\HttpClientInterface::class));
        $result = $service->tables(["a\tb", "c\td"]);
        $this->assertSame([['a', 'b'], ['c', 'd']], $result);
    }
}
