<?php

namespace Labstag\Scheduler;

use Labstag\Message\BanIpMessage;
use Labstag\Message\PageCinemaMessage;
use Labstag\Message\UpdateSerieMessage;
use Symfony\Component\Scheduler\Attribute\AsSchedule;
use Symfony\Component\Scheduler\RecurringMessage;
use Symfony\Component\Scheduler\Schedule;
use Symfony\Component\Scheduler\ScheduleProviderInterface;
use Symfony\Contracts\Cache\CacheInterface;

#[AsSchedule('cinema')]
final class PageCinemaSchedule implements ScheduleProviderInterface
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
            RecurringMessage::cron('0 0 * * 1', new PageCinemaMessage()),
            RecurringMessage::cron('0 */1 * * *', new BanIpMessage()),
            RecurringMessage::cron('0 10 * * *', new UpdateSerieMessage()),
            // RecurringMessage::every('1 minute', new PageCinemaMessage()),
        );
        $schedule->stateful($this->cache);

        return $schedule;
    }
}
