<?php

namespace Labstag\Messenger\Middleware;

use Labstag\Messenger\Stamp\CorrelationIdStamp;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Middleware\MiddlewareInterface;
use Symfony\Component\Messenger\Middleware\StackInterface;

class CorrelationIdMiddleware implements MiddlewareInterface
{
    public function handle(Envelope $envelope, StackInterface $stack): Envelope
    {
        if (null === $envelope->last(CorrelationIdStamp::class)) {
            $envelope = $envelope->with(new CorrelationIdStamp());
        }

        return $stack->next()->handle($envelope, $stack);
    }
}