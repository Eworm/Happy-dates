<?php

namespace Statamic\Addons\HappyDates;

use Illuminate\Support\Facades\Storage;
use Statamic\Extend\Tasks;
use Illuminate\Console\Scheduling\Schedule;

class HappyDatesTasks extends Tasks
{
    /**
     * Define the task schedule
     *
     * @param \Illuminate\Console\Scheduling\Schedule $schedule
     */
    public function schedule(Schedule $schedule)
    {

        $schedule->command('happydates:refresh')->everyMinute();

        $feeds_storage  = Storage::files('/site/storage/addons/HappyDates');

        foreach ($feeds_storage as $feed) {

            $rem = str_replace('site/storage/addons/HappyDates/', '', $feed);

            $ignore = array( 'cgi-bin', '.', '..','._' );
            if (!in_array($rem, $ignore) and substr($rem, 0, 1) != '.') {

                $info = $this->storage->getYaml($rem);
                $int = intval($info['scheduling']);

                $schedule->command('happydates:refresh')->cron('*/' . $int . ' * * * * *');

            }
        }
    }
}
