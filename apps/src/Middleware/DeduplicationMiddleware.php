<?php

namespace Labstag\Middleware;

use Labstag\Service\MessageDispatcherService;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Middleware\MiddlewareInterface;
use Symfony\Component\Messenger\Middleware\StackInterface;

/**
 * Middleware qui empêche le dispatch de messages dupliqués.
 */
final class DeduplicationMiddleware implements MiddlewareInterface
{
    public function __construct(
        private MessageDispatcherService $messageDispatcherService,
    )
    {
    }

    public function handle(Envelope $envelope, StackInterface $stack): Envelope
    {
        $message    = $envelope->getMessage();
        $messageKey = $this->getMessageKey($message);

        // Si le message a déjà été dispatché, on ne le traite pas
        if ($this->messageDispatcherService->isAlreadyDispatched($messageKey)) {
            return $envelope;
        }

        // Marquer comme dispatché
        $this->messageDispatcherService->markAsDispatched($messageKey);

        // Continuer le traitement
        return $stack->next()->handle($envelope, $stack);
    }

    private function getMessageKey(object $message): string
    {
        $className  = get_class($message);
        $reflection = new \ReflectionClass($message);
        $properties = [];

        foreach ($reflection->getProperties() as $property) {
            $property->setAccessible(true);
            $value = $property->getValue($message);
            $properties[$property->getName()] = $this->serializeValue($value);
        }

        ksort($properties);

        return $className . '::' . md5(serialize($properties));
    }

    private function serializeValue(mixed $value): mixed
    {
        if (is_object($value)) {
            return spl_object_hash($value);
        }

        return $value;
    }
}
