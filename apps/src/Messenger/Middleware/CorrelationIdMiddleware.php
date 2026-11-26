<?php

declare(strict_types=1);

namespace Labstag\Messenger\Middleware;

use Labstag\Messenger\Stamp\CorrelationIdStamp;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Middleware\MiddlewareInterface;
use Symfony\Component\Messenger\Middleware\StackInterface;
use Symfony\Component\Messenger\Stamp\StampInterface;

class CorrelationIdMiddleware implements MiddlewareInterface
{
    public function handle(Envelope $envelope, StackInterface $stack): Envelope
    {
        if (!$envelope->last(CorrelationIdStamp::class) instanceof StampInterface) {
            $envelope = $envelope->with(new CorrelationIdStamp());
        }

        return $stack->next()->handle($envelope, $stack);
    }
}
