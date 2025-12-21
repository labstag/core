<?php

namespace Labstag\Service;

use Symfony\Component\Messenger\MessageBusInterface;

/**
 * Service de dispatch de messages avec déduplication.
 * Évite de dispatcher plusieurs fois le même message identique dans une même requête.
 */
final class MessageDispatcherService
{
    /**
     * @var array<string, array<string, bool>>
     */
    private array $dispatchedMessages = [];

    public function __construct(
        private MessageBusInterface $messageBus,
    )
    {
    }

    /**
     * Dispatch un message seulement s'il n'a pas déjà été dispatché.
     *
     * @param object $message      Le message à dispatcher
     * @param array  $stamps       Les stamps Symfony Messenger optionnels
     * @param bool   $forceDispatch Force le dispatch même si déjà dispatché
     */
    public function dispatch(object $message, array $stamps = [], bool $forceDispatch = false): void
    {
        $messageKey = $this->getMessageKey($message);

        if (!$forceDispatch && $this->isAlreadyDispatched($messageKey)) {
            return;
        }

        $this->markAsDispatched($messageKey);
        $this->messageBus->dispatch($message, $stamps);
    }

    /**
     * Vérifie si un message a déjà été dispatché.
     */
    public function isAlreadyDispatched(string $messageKey): bool
    {
        $className = $this->extractClassName($messageKey);

        return isset($this->dispatchedMessages[$className][$messageKey]);
    }

    /**
     * Marque un message comme dispatché.
     */
    public function markAsDispatched(string $messageKey): void
    {
        $className = $this->extractClassName($messageKey);
        if (!isset($this->dispatchedMessages[$className])) {
            $this->dispatchedMessages[$className] = [];
        }

        $this->dispatchedMessages[$className][$messageKey] = true;
    }

    /**
     * Réinitialise le cache des messages dispatchés.
     * Utile pour les tests ou pour réinitialiser l'état entre différentes requêtes.
     */
    public function reset(): void
    {
        $this->dispatchedMessages = [];
    }

    /**
     * Génère une clé unique pour un message basée sur sa classe et ses propriétés.
     */
    private function getMessageKey(object $message): string
    {
        $className  = get_class($message);
        $reflection = new \ReflectionClass($message);
        $properties = [];

        foreach ($reflection->getProperties() as $property) {
            $property->setAccessible(true);
            $value = $property->getValue($message);
            // Convertir les valeurs en string pour la comparaison
            $properties[$property->getName()] = $this->serializeValue($value);
        }

        ksort($properties);

        return $className . '::' . md5(serialize($properties));
    }

    /**
     * Extrait le nom de classe depuis une clé de message.
     */
    private function extractClassName(string $messageKey): string
    {
        return explode('::', $messageKey)[0];
    }

    /**
     * Sérialise une valeur pour la comparaison.
     *
     * @return mixed
     */
    private function serializeValue(mixed $value): mixed
    {
        if (is_object($value)) {
            return spl_object_hash($value);
        }

        return $value;
    }

    /**
     * Retourne le nombre de messages dispatchés par type.
     *
     * @return array<string, int>
     */
    public function getDispatchStats(): array
    {
        $stats = [];
        foreach ($this->dispatchedMessages as $className => $messages) {
            $stats[$className] = count($messages);
        }

        return $stats;
    }
}
