<?php

namespace App\Scheduler\Message;

class AutoDelete
{
    public function __construct(public int $length)
    {
    }
}
