<?php

declare(strict_types=1);

namespace Labstag\Tests\Unit\Service;

use Labstag\Service\ShortCodeService;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class ShortCodeServiceTest extends TestCase
{

    #[Test]
    #[Group('unit')]
    public function changeContentNoShortcodes(): void
    {
        $service = new ShortCodeService([]);
        $this->assertSame('hello', $service->changeContent('hello'));
    }

    #[Test]
    #[Group('unit')]
    public function getContentNoMatchReturnsUrl(): void
    {
        $service = new ShortCodeService([]);
        $this->assertSame('http://x', $service->getContent('http://x'));
    }
}
