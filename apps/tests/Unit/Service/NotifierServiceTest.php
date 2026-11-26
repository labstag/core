<?php

declare(strict_types=1);

namespace Labstag\Tests\Unit\Service;

use Labstag\Service\NotifierService;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Symfony\Component\Notifier\ChatterInterface;

final class NotifierServiceTest extends TestCase
{
    #[Test]
    #[Group('unit')]
    public function sendMessageCallsChatter(): void
    {
        $chatter = $this->createMock(ChatterInterface::class);
        $chatter->expects($this->once())->method('send');
        $logger  = $this->createStub(LoggerInterface::class);
        $service = new NotifierService($chatter, $logger);
        $service->sendMessage('hi');
    }
}
