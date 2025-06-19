<?php

namespace App\Scheduler;

use App\Scheduler\Message\AutoDelete;
use Symfony\Component\Scheduler\Attribute\AsSchedule;
use Symfony\Component\Scheduler\RecurringMessage;
use Symfony\Component\Scheduler\Schedule;
use Symfony\Component\Scheduler\ScheduleProviderInterface;
use Symfony\Contracts\Cache\CacheInterface;

#[AsSchedule]
class MainSchedule implements ScheduleProviderInterface
{
    public function __construct(
        private CacheInterface $cache,
    )
    {
    }

    public function getSchedule(): Schedule
    {
        return new Schedule()->add(
            RecurringMessage::every('1 minutes', new AutoDelete(1)),
        )
            ->stateful($this->cache);
    }
}
