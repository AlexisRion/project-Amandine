<?php

namespace App\Scheduler;

use Symfony\Component\Scheduler\Attribute\AsSchedule;
use Symfony\Component\Scheduler\Schedule;
use Symfony\Component\Scheduler\ScheduleProviderInterface;

class AutoDeleteProvider
{
    public function getSchedule(): Schedule
    {
        // ...
    }
}
