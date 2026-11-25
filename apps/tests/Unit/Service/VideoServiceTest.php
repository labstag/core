<?php

declare(strict_types=1);

namespace Labstag\Tests\Unit\Service;

use Labstag\Service\VideoService;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

final class VideoServiceTest extends TestCase
{

    private LoggerInterface&MockObject $logger;

    private VideoService $service;

    #[Test]
    #[Group('unit')]
    public function getTrailerNullWhenNoResults(): void
    {
        $this->assertNull($this->service->getTrailer([]));
    }

    #[Test]
    #[Group('unit')]
    public function getTrailerUnsupportedLogs(): void
    {
        $details = [
            'results' => [
                [
                    'type' => 'Trailer',
                    'site' => 'Foo',
                    'key'  => 'abc',
                ],
            ],
        ];
        $this->logger->expects($this->exactly(2))->method('warning');
        $this->assertNull($this->service->getTrailer($details));
    }

    #[Test]
    #[Group('unit')]
    public function getTrailerYoutube(): void
    {
        $details = [
            'results' => [
                [
                    'type' => 'Trailer',
                    'site' => 'YouTube',
                    'key'  => 'abc',
                ],
            ],
        ];
        $this->assertSame('https://www.youtube.com/watch?v=abc', $this->service->getTrailer($details));
    }

    protected function setUp(): void
    {
        $this->logger  = $this->createMock(LoggerInterface::class);
        $this->service = new VideoService($this->logger);
    }
}
