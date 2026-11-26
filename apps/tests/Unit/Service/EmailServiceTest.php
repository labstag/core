<?php

declare(strict_types=1);

namespace Labstag\Tests\Unit\Service;

use Labstag\Service\EmailService;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Mailer\MailerInterface;

final class EmailServiceTest extends TestCase
{
    #[Test]
    #[Group('unit')]
    public function allReturnsIterable(): void
    {
        $service = new EmailService([], $this->createStub(MailerInterface::class));
        $this->assertSame([], iterator_to_array($service->all()));
    }
}
