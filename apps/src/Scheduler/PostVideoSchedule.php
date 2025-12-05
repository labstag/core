<?php

namespace Labstag\Scheduler;

use Labstag\Message\PostVideoMessage;
use Symfony\Component\Scheduler\Attribute\AsSchedule;
use Symfony\Component\Scheduler\RecurringMessage;
use Symfony\Component\Scheduler\Schedule;
use Symfony\Component\Scheduler\ScheduleProviderInterface;
use Symfony\Contracts\Cache\CacheInterface;

#[AsSchedule('cinema')]
final class PostVideoSchedule implements ScheduleProviderInterface
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
            // RecurringMessage::cron('0 0 * * 1', new PostVideoMessage()),
            RecurringMessage::every('1 minute', new PostVideoMessage()),
        );
        $schedule->stateful($this->cache);

        return $schedule;
    }
}
