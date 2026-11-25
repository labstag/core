<?php

declare(strict_types=1);

namespace Labstag\Tests\Unit\Service;

use Labstag\Service\EmailService;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class EmailServiceTest extends TestCase
{

    #[Test]
    #[Group('unit')]
    public function allReturnsIterable(): void
    {
        $service = new EmailService([], $this->createMock(\Symfony\Component\Mailer\MailerInterface::class));
        $this->assertSame([], iterator_to_array($service->all()));
    }
}
