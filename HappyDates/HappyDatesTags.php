<?php

namespace Statamic\Addons\HappyDates;

use Statamic\Addons\HappyDates\iCal;
use Recurr\Rule;
use Recurr\Transformer;
use Spatie\CalendarLinks\Link;
use Statamic\Contracts\Forms\Submission;
use Statamic\API\Form;
use Statamic\API\Entry;
use Statamic\Extend\Collection;
use Statamic\Extend\Tags;
use Statamic\API\Folder;
use Statamic\API\Config;
use Illuminate\Support\Facades\Storage;
use Statamic\Data\DataCollection;
use Statamic\API\File;
use Statamic\API\Yaml;
use Illuminate\Http\Request;
use Carbon\Carbon;

class HappyDatesTags extends Tags
{
    /**
     * The {{ happy_dates }} tag
     *
     * @return string|array
     */
    public function index()
    {
        //
    }

    /**
     * The {{ happy_dates:start_date }} tag
     *
     * @return string
     */
    private function startDate()
    {
        if (isset($this->context['pw_start_date'])) {
            return $this->context['pw_start_date'];
        } else {
            return null;
        }
    }

    /**
     * The {{ happy_dates:end_date }} tag
     *
     * @return string
     */
    private function endDate()
    {
        if (isset($this->context['pw_end_date'])) {
            return $this->context['pw_end_date'];
        } else {
            return null;
        }
    }

    /**
     * The {{ happy_dates:recurring }} tag
     *
     * @return array
     */
    public function recurring()
    {
        if ($this->context['pw_recurring'] == true)
        {
            $timezone  = $this->context['settings']['system']['timezone'];
            $startdate = new \DateTime($this->startDate());
            $enddate = new \DateTime($this->endDate());

            $newrule = '';

            if (isset($this->context['pw_recurring_ends'])) {
                $ends = $this->context['pw_recurring_ends'];
            }
            if (isset($this->context['pw_recurring_frequency'])) {
                $frequency = $this->context['pw_recurring_frequency'];
                $newrule .= 'FREQ=' . $frequency;
            }
            if (isset($this->context['pw_recurring_byday'])) {
                $byday = $this->context['pw_recurring_byday'];
                $newrule .= ';BYDAY=' . \implode(',',$byday);
            }
            if (isset($this->context['pw_recurring_count'])) {
                $count = $this->context['pw_recurring_count'];
                $newrule .= ';COUNT=' . $count;
            }
            if (isset($this->context['pw_recurring_until'])) {
                $until = new \DateTime($this->context['pw_recurring_until']);
                $newrule .= ';UNTIL=' . $until->format('Y-m-d');
            }
            if (isset($this->context['pw_recurring_interval'])) {
                $interval = $this->context['pw_recurring_interval'];
                $newrule .= ';INTERVAL=' . $interval;
            }
            $transformer = new Transformer\ArrayTransformer();
            $rule = new \Recurr\Rule($newrule, $startdate, $enddate, $timezone);

            $ruledates = $transformer->transform($rule);
            $dates = [];

            foreach ($ruledates as $date)
            {
                $startdate = $date->getStart();
                $carbon_start = $this->dtCarbon($startdate);
                $enddate = $date->getEnd();
                $carbon_end = $this->dtCarbon($enddate);

                $item = array(
                    'start' => $carbon_start->toDateTimeString(),
                    'end' => $carbon_end->toDateTimeString()
                );
                $dates[] = $item;
            }
            return $this->parseLoop($dates);
        }
    }

    /**
     * The {{ happy_dates:calendar }} tag
     *
     * @return string
     */
    public function calendar()
    {
        $feed_storage = $this->getParam('feed');
        $feeds_storage = Storage::files('/site/storage/addons/HappyDates');
        $data = [];

        if ($feed_storage)
        {
            $data = $this->getEvents($feed_storage, $data);
        }
        else
        {
            foreach ($feeds_storage as $feed)
            {
                $st_feed = str_replace('site/storage/addons/HappyDates/', '', $feed);
                $ignore = array( 'cgi-bin', '.', '..','._' );
                if (!in_array($st_feed, $ignore) and substr($st_feed, 0, 1) != '.')
                {
                    $data = $this->getEvents($st_feed, $data);
                }
            }
        }

        usort($data, array($this, 'dateSort'));
        return $this->parseLoop($data);
    }

    /**
     * Get all events
     *
     * @return array
     */
    private function getEvents($feed, $data)
    {
        $settings = $this->getFromStorage($feed);
        $ical = new iCal();
        $ical = $this->getFromCache($ical, $settings['title']);
        $events = $ical->eventsByDate();

        foreach ($events as $date => $days)
        {
            foreach ($days as $event)
            {
                $data[] = $this->addEventData($event);
            }
        }
        return $data;
    }

    /**
     * Add event data
     *
     * @return array
     */
    private function addEventData($event)
    {
        return [
            'title' => $event['event']->title(),
            'status' => $event['event']->status,
            'location' => $event['event']->location,
            'created' => $event['event']->created,
            'updated' => $event['event']->updated,
            'start_date' => $event['date']
        ];
    }

    /**
     * Transform a datetime object to Carbon
     *
     * @return string
     */
    private function dtCarbon($date)
    {
        return Carbon::instance($date);
    }

    /**
     * Transform a datetime object to Carbon
     *
     * @return string
     */
    private static function dateSort($a, $b)
    {
        if ($a['start_date'] == $b['start_date'])
        {
            return 0;
        }
        return ($a['start_date'] < $b['start_date']) ? -1 : 1;
    }

    /**
     * Get a file from storage
     *
     * @return yaml
     */
    private function getFromStorage($file)
    {
        return $this->storage->getYaml($file);
    }

    /**
     * Get a file from cache
     *
     * @return yaml
     */
    private function getFromCache($ical, $title)
    {
        return $ical->cache($this->cache->get(slugify($title)));
    }
}
