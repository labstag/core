<?php

namespace Labstag\Service;

use Exception;
use Psr\Log\LoggerInterface;
use Symfony\Component\Notifier\Bridge\Telegram\TelegramOptions;
use Symfony\Component\Notifier\ChatterInterface;
use Symfony\Component\Notifier\Message\ChatMessage;

final class NotifierService
{
    public function __construct(
        private ChatterInterface $chatter,
        private LoggerInterface $logger,
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
        $telegramOptions = new TelegramOptions();
        $this->telegram($message, $telegramOptions);
    }

    private function telegram(string $message, TelegramOptions $telegramOptions): void
    {
        try {
            $chatMessage = new ChatMessage($message);
            $chatMessage->options($telegramOptions);
            $this->chatter->send($chatMessage);
        } catch (Exception $exception) {
            $this->logger->error($exception->getMessage());
        }
    }
}
