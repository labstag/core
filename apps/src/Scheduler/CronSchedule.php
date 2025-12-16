<?php

namespace Labstag\Scheduler;

use Labstag\Message\BanIpMessage;
use Labstag\Message\FilesMessage;
use Labstag\Message\MetaMessage;
use Labstag\Message\NotificationMessage;
use Labstag\Message\UpdateSerieMessage;
use Symfony\Component\Scheduler\Attribute\AsSchedule;
use Symfony\Component\Scheduler\RecurringMessage;
use Symfony\Component\Scheduler\Schedule;
use Symfony\Component\Scheduler\ScheduleProviderInterface;
use Symfony\Contracts\Cache\CacheInterface;

#[AsSchedule('cron')]
final class CronSchedule implements ScheduleProviderInterface
{
    public function __construct(
        private CacheInterface $cache,
    )
    {
    }

    public function getSchedule(): Schedule
    {
        $schedule = new Schedule();
        $schedule->add(
            RecurringMessage::cron('0 */1 * * *', new BanIpMessage()),
            RecurringMessage::cron('0 10 * * *', new UpdateSerieMessage()),
            RecurringMessage::cron('0 12 * * *', new NotificationMessage()),
            RecurringMessage::cron('0 0 * * 6', new FilesMessage()),
            RecurringMessage::cron('0 20 * * *', new MetaMessage()),
            // RecurringMessage::every('1 minute', new PageCinemaMessage()),
        );
        $schedule->stateful($this->cache);

        return $schedule;
    }
}
