<?php

namespace Labstag\Service;

use Exception;
use RuntimeException;
use Symfony\Component\Notifier\Bridge\Bluesky\BlueskyOptions;
use Symfony\Component\Notifier\Bridge\Discord\DiscordOptions;
use Symfony\Component\Notifier\Bridge\Mastodon\MastodonOptions;
use Symfony\Component\Notifier\ChatterInterface;
use Symfony\Component\Notifier\Message\ChatMessage;

final class NotifierService
{
    public function __construct(
        private ChatterInterface $chatter
    )
    {

    }

    public function sendMessage(string $message): void
    {
        $this->bluesky($message);
        $this->discord($message);
        $this->mastodon($message);
    }

    private function discord(string $message): void
    {
        try{
            $chatMessage = new ChatMessage($message);
            $options = new DiscordOptions();
            $chatMessage->options($options);
            $this->chatter->send($chatMessage);
        }
        catch (Exception $e) {
            throw new RuntimeException($e->getMessage());
        }
    }

    private function bluesky(string $message): void
    {
        try{
            $chatMessage = new ChatMessage($message);
            $options = new BlueskyOptions();
            $chatMessage->options($options);
            $this->chatter->send($chatMessage);
        }
        catch (Exception $e) {
            throw new RuntimeException($e->getMessage());
        }
    }

    private function mastodon(string $message): void
    {
        try{
            $chatMessage = new ChatMessage($message);
            $options = new MastodonOptions();
            $chatMessage->options($options);
            $this->chatter->send($chatMessage);
        }
        catch (Exception $e) {
            throw new RuntimeException($e->getMessage());
        }
    }
}