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
            RecurringMessage::every('12 hours', new AutoDelete()), // 12 hours so if a delete fails it can retry once the same day
        )
            ->stateful($this->cache) // Permit to do tasks that was planned but not executed (to uncomment when schedule works)
            ;
    }
}
