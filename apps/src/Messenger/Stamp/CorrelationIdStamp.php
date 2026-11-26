<?php

declare(strict_types=1);

namespace Labstag\Messenger\Stamp;

use Symfony\Component\Messenger\Stamp\StampInterface;
use Symfony\Component\Uid\Ulid;

class CorrelationIdStamp implements StampInterface
{

    public readonly string $correlationId;

    public function __construct(?string $correlationId = null)
    {
        $this->correlationId = $correlationId ?? (string) new Ulid();
    }
}
