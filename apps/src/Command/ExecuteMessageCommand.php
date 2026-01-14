<?php

namespace Labstag\Command;

use Labstag\Message\BanIpMessage;
use Labstag\Message\DeleteOldFileMessage;
use Labstag\Message\MetaAllMessage;
use Labstag\Message\MovieAllMessage;
use Labstag\Message\NotificationMessage;
use Labstag\Message\PageCinemaMessage;
use Labstag\Message\PersonAllMessage;
use Labstag\Message\SerieAllMessage;
use Labstag\Message\UpdateSerieMessage;
use Labstag\Service\MessageDispatcherService;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(name: 'labstag:execute:message', description: 'Execute selected messages',)]
class ExecuteMessageCommand
{
    public function __construct(
        protected MessageDispatcherService $messageBus,
    )
    {
    }

    public function __invoke(SymfonyStyle $symfonyStyle): int
    {
        $choices = [
            'PageCinema'   => 'Generate cinema pages',
            'BanIp'        => 'Ban IP addresses',
            'Series'  => 'Update series',
            'Notification' => 'Send notifications',
            'Meta'         => 'Clean meta entries',
            'Person'       => 'Update persons',
            'Movie'        => 'Update movies',
            'Files'        => 'Clean files',
            'All'          => 'Execute all tasks',
            'Cancel'       => 'Cancel execution',
        ];

        $selected = $symfonyStyle->choice('Which task do you want to execute?', array_values($choices), 8);

        $selectedKey = array_search($selected, $choices, true);

        $messages = [
            'PageCinema'   => PageCinemaMessage::class,
            'BanIp'        => BanIpMessage::class,
            'Series'       => [UpdateSerieMessage::class, SerieAllMessage::class],
            'Meta'         => MetaAllMessage::class,
            'Person'       => PersonAllMessage::class,
            'Movie'        => MovieAllMessage::class,
            'Files'        => DeleteOldFileMessage::class,
            'Notification' => NotificationMessage::class,
        ];

        $toExecute = 'All' === $selectedKey ? array_keys($messages) : [$selectedKey];
        if ('Cancel' === $selectedKey) {
            $symfonyStyle->warning('Execution cancelled by user.');

            return Command::SUCCESS;
        }

        foreach ($toExecute as $key) {
            $symfonyStyle->section(sprintf('Dispatching %sMessage', $key));
            $messageClasses = is_array($messages[$key]) ? $messages[$key] : [$messages[$key]];
            foreach ($messageClasses as $messageClass) {
                $this->messageBus->dispatch(new $messageClass());
            }
        }

        return Command::SUCCESS;
    }
}
