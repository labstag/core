<?php

namespace Labstag\Service;

use Exception;
use Psr\Log\LoggerInterface;
use Symfony\Component\Notifier\Bridge\Bluesky\BlueskyOptions;
use Symfony\Component\Notifier\Bridge\Discord\DiscordOptions;
use Symfony\Component\Notifier\Bridge\Mastodon\MastodonOptions;
use Symfony\Component\Notifier\Bridge\Telegram\TelegramOptions;
use Symfony\Component\Notifier\ChatterInterface;
use Symfony\Component\Notifier\Message\ChatMessage;

final class NotifierService
{
    public function __construct(
        private ChatterInterface $chatter,
        private LoggerInterface $logger
    )
    {

    }

    public function sendMessage(string $message): void
    {
        // $options = new BlueskyOptions();
        // $this->bluesky($message, $options);
        // $options = new DiscordOptions();
        // $this->discord($message, $options);
        // $options = new MastodonOptions();
        // $this->mastodon($message, $options);
        $options = new TelegramOptions();
        $this->telegram($message, $options);
    }

    private function telegram(string $message, $options): void
    {
        try{
            $chatMessage = new ChatMessage($message);
            $chatMessage->options($options);
            $this->chatter->send($chatMessage);
        }
        catch (Exception $e) {
            $this->logger->error($e->getMessage());
        }
    }

    private function discord(string $message, $options): void
    {
        try{
            $chatMessage = new ChatMessage($message);
            $chatMessage->options($options);
            $this->chatter->send($chatMessage);
        }
        catch (Exception $e) {
            $this->logger->error($e->getMessage());
        }
    }

    private function bluesky(string $message, $options): void
    {
        try{
            $chatMessage = new ChatMessage($message);
            $chatMessage->options($options);
            $this->chatter->send($chatMessage);
        }
        catch (Exception $e) {
            $this->logger->error($e->getMessage());
        }
    }

    private function mastodon(string $message, $options): void
    {
        try{
            $chatMessage = new ChatMessage($message);
            $chatMessage->options($options);
            $this->chatter->send($chatMessage);
        }
        catch (Exception $e) {
            $this->logger->error($e->getMessage());
        }
    }
}