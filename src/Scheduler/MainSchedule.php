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
            RecurringMessage::every('10 seconds', new AutoDelete()),
        )
//            ->stateful($this->cache) // Permit to do tasks that was planned but not executed
            ;
    }
}
