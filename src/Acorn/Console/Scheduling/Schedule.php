<?php

namespace Roots\Acorn\Console\Scheduling;

use Illuminate\Console\Scheduling\Schedule as ScheduleBase;
use Illuminate\Contracts\Cache\Repository as Cache;

class Schedule extends ScheduleBase
{
    /**
     * Create a new Schedule instance.
     *
     * @param  \Illuminate\Contracts\Cache\Repository $cache
     * @return void
     */
    public function __construct(Cache $cache)
    {
        $this->cache = $cache;
    }
}
