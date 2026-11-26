<?php

declare(strict_types=1);

namespace Labstag\Tests\Unit\Service;

use stdClass;
use Labstag\Service\MessageDispatcherService;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\MessageBusInterface;

final class MessageDispatcherServiceTest extends TestCase
{
    #[Test]
    #[Group('unit')]
    public function dedupes(): void
    {
        $bus     = new class implements MessageBusInterface {

            public int $calls = 0;
            public function dispatch(object $message, array $stamps = []): Envelope
            {
                ++$this->calls;
                return new Envelope($message);
            }
        };
        $service = new MessageDispatcherService($bus);
        $msg     = new stdClass();
        $service->dispatch($msg);
        $service->dispatch($msg);
        $this->assertSame(1, $bus->calls);
    }

    #[Test]
    #[Group('unit')]
    public function dispatchCallsBus(): void
    {
        $bus     = new class implements MessageBusInterface {

            public int $calls = 0;
            public function dispatch(object $message, array $stamps = []): Envelope
            {
                ++$this->calls;
                return new Envelope($message);
            }
        };
        $service = new MessageDispatcherService($bus);
        $service->dispatch(new stdClass());
        $this->assertSame(1, $bus->calls);
    }

    #[Test]
    #[Group('unit')]
    public function stats(): void
    {
        $bus     = new class implements MessageBusInterface {

            public int $calls = 0;
            public function dispatch(object $message, array $stamps = []): Envelope
            {
                ++$this->calls;
                return new Envelope($message);
            }
        };
        $service = new MessageDispatcherService($bus);
        $service->dispatch(new stdClass());
        $this->assertNotEmpty($service->getDispatchStats());
    }
}
