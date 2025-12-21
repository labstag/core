<?php

namespace Labstag\Command;

use Labstag\Message\BanIpMessage;
use Labstag\Message\FilesMessage;
use Labstag\Message\MetaMessage;
use Labstag\Message\NotificationMessage;
use Labstag\Message\PageCinemaMessage;
use Labstag\Message\UpdateSerieMessage;
use Labstag\Service\MessageDispatcherService;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Messenger\MessageBusInterface;

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
            'UpdateSerie'  => 'Update series',
            'Notification' => 'Send notifications',
            'Meta'         => 'Clean meta entries',
            'Files'        => 'Clean files',
            'All'          => 'Execute all tasks',
            'Cancel'       => 'Cancel execution',
        ];

        $selected = $symfonyStyle->choice('Which task do you want to execute?', array_values($choices), 6);

        $selectedKey = array_search($selected, $choices, true);

        $messages = [
            'PageCinema'   => PageCinemaMessage::class,
            'BanIp'        => BanIpMessage::class,
            'UpdateSerie'  => UpdateSerieMessage::class,
            'Meta'         => MetaMessage::class,
            'Files'        => FilesMessage::class,
            'Notification' => NotificationMessage::class,
        ];

        $toExecute = 'All' === $selectedKey ? array_keys($messages) : [$selectedKey];
        if ('Cancel' === $selectedKey) {
            $symfonyStyle->warning('Execution cancelled by user.');

            return Command::SUCCESS;
        }

        foreach ($toExecute as $key) {
            $symfonyStyle->section(sprintf('Dispatching %sMessage', $key));
            $this->messageBus->dispatch(new $messages[$key]());
        }

        return Command::SUCCESS;
    }
}
