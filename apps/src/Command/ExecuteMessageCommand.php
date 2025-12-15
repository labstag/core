<?php

namespace Labstag\Command;

use Labstag\Message\BanIpMessage;
use Labstag\Message\NotificationMessage;
use Labstag\Message\PageCinemaMessage;
use Labstag\Message\UpdateSerieMessage;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Messenger\MessageBusInterface;

#[AsCommand(name: 'labstag:execute:message', description: 'Execute selected messages',)]
class ExecuteMessageCommand
{
    public function __construct(
        protected MessageBusInterface $messageBus,
    )
    {
    }

    public function __invoke(SymfonyStyle $symfonyStyle): int
    {
        $choices = [
            'PageCinema' => 'Generate cinema pages',
            'BanIp' => 'Ban IP addresses',
            'UpdateSerie' => 'Update series',
            'Notification' => 'Send notifications',
            'All' => 'Execute all tasks',
            'Cancel' => 'Cancel execution',
        ];

        $selected = $symfonyStyle->choice(
            'Which task do you want to execute?',
            array_values($choices),
            5
        );

        $selectedKey = array_search($selected, $choices);

        $messages = [
            'PageCinema' => PageCinemaMessage::class,
            'BanIp' => BanIpMessage::class,
            'UpdateSerie' => UpdateSerieMessage::class,
            'Notification' => NotificationMessage::class,
        ];

        $toExecute = $selectedKey === 'All' ? array_keys($messages) : [$selectedKey];
        if ($selectedKey === 'Cancel') {
            $symfonyStyle->warning('Execution cancelled by user.');
            return Command::SUCCESS;
        }

        foreach ($toExecute as $key) {
            $symfonyStyle->section("Dispatching {$key}Message");
            $this->messageBus->dispatch(new $messages[$key]());
        }

        return Command::SUCCESS;
    }
}
