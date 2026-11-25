<?php

declare(strict_types=1);

namespace Labstag\Tests\Unit\Service;

use Labstag\Service\FormService;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class FormServiceTest extends TestCase
{

    #[Test]
    #[Group('unit')]
    public function allEmpty(): void
    {
        $service = new FormService([]);
        $this->assertSame([], $service->all());
    }

    #[Test]
    #[Group('unit')]
    public function getNullWhenEmpty(): void
    {
        $service = new FormService([]);
        $this->assertNull($service->get('x'));
    }
}
