<?php

namespace Statamic\Addons\IcalImport;

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

class IcalImportTags extends Tags
{
    /**
     * The {{ ical_import }} tag
     *
     * @return string|array
     */
    public function index()
    {
        //
    }

    /**
     * The {{ ical_import:start_date }} tag
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
     * The {{ ical_import:end_date }} tag
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
     * The {{ ical_import:recurring }} tag
     *
     * @return array
     */
    public function recurring()
    {
        if ($this->context['pw_recurring'] == true) {
            if ($this->context['pw_recurring_frequency'] != 'CUSTOM') {
                $timezone  = $this->context['settings']['system']['timezone'];
                $startdate = new \DateTime($this->startDate());
                $enddate = new \DateTime($this->endDate());

                if (isset($this->context['pw_recurring_ends'])) {
                    $ends = $this->context['pw_recurring_ends'];
                }
                if (isset($this->context['pw_recurring_frequency'])) {
                    $frequency = $this->context['pw_recurring_frequency'];
                }
                if (isset($this->context['pw_recurring_count'])) {
                    $count = $this->context['pw_recurring_count'];
                }
                if (isset($this->context['pw_recurring_until'])) {
                    $until = new \DateTime($this->context['pw_recurring_until']);
                }
                if (isset($this->context['pw_recurring_interval'])) {
                    $interval = $this->context['pw_recurring_interval'];
                }

                $transformer = new Transformer\ArrayTransformer();

                if ($ends == 'after') {
                    $rule = (new \Recurr\Rule)
                        ->setStartDate($startdate)
                        ->setEndDate($enddate)
                        ->setTimezone($timezone)
                        ->setCount($count)
                        ->setInterval($interval)
                        ->setFreq($frequency);
                } else {
                    $rule = (new \Recurr\Rule)
                        ->setStartDate($startdate)
                        ->setEndDate($enddate)
                        ->setTimezone($timezone)
                        ->setFreq($frequency)
                        ->setInterval($interval)
                        ->setUntil($until);
                }

                $ruledates = $transformer->transform($rule);
                $dates = [];

                foreach ($ruledates as $date) {
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
            } else {
                $customdates = $this->context['pw_recurring_manual'];
                $dates = [];

                // Add the start & end date of the current event
                $dates[] = array(
                    'start' => $this->startDate(),
                    'end' => $this->endDate()
                );

                foreach ($customdates as $date) {
                    $startdate = $date['pw_recurring_manual_start'];
                    $enddate = $date['pw_recurring_manual_end'];

                    $item = array(
                        'start' => $startdate,
                        'end' => $enddate
                    );
                    $dates[] = $item;
                }
                return $this->parseLoop($dates);
            }
        }
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
}
