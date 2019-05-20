<?php

namespace Statamic\Addons\IcalImport;

use Statamic\Extend\Tasks;
use Illuminate\Console\Scheduling\Schedule;

class IcalImportTasks extends Tasks
{
    /**
     * Define the task schedule
     *
     * @param \Illuminate\Console\Scheduling\Schedule $schedule
     */
    public function schedule(Schedule $schedule)
    {
        // $schedule->command('cache:clear')->weekly();
    }
}
