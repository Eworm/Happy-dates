<?php

namespace Statamic\Addons\HappyDates;

use Statamic\Extend\Filter;
use Carbon\Carbon;

class HappyDatesFilter extends Filter
{

    /**
     * Perform filtering on a collection
     *
     * @return \Illuminate\Support\Collection
     */
    public function filter()
    {
        //
        $remove = $this->getParam('remove');

        if ($remove == 'future') {
            // Return future events
            return $this->collection->filter(function ($entry) {
                if ($entry->get('pw_start_date')) {
                    return (new Carbon($entry->get('pw_start_date')))->lt(Carbon::now());
                }
            });

        } else if ($remove == 'past') {
            // Return past events
            return $this->collection->filter(function ($entry) {
                if ($entry->get('pw_start_date')) {
                    return (new Carbon($entry->get('pw_start_date')))->gt(Carbon::now());
                }
            });

        }
    }
}
