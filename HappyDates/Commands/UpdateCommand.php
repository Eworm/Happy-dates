<?php

namespace Statamic\Addons\HappyDates\Commands;

use Statamic\API\Str;
use Statamic\Addons\HappyDates\iCal;
use Illuminate\Support\Facades\Storage;
use Statamic\API\Entry;
use Statamic\API\File;
use Statamic\Extend\Command;

class UpdateCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'happydates:refresh';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Updates all events';

    /**
     * Create a new command instance.
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $events_storage  = Storage::files('/site/storage/addons/HappyDates');

        foreach ($events_storage as $event) {
            $st_event = str_replace('site/storage/addons/HappyDates/', '', $event);
            $ignore = array( 'cgi-bin', '.', '..','._' );

            if (!in_array($st_event, $ignore) and substr($st_event, 0, 1) != '.') {
                $settings   = $this->storage->getYaml($st_event);
                $url        = $settings['url'];
                $enabled    = $settings['enabled'];

                // Add the last updated time to the feed info
                $settings['updated'] = time();
                $this->storage->putYAML($st_event, $settings);

                $this->info('Updating ' . $settings['title']);
                $i = 0;

                if ($enabled == true) {

                    $ical = new iCal($url);
                    $events = $ical->eventsByDate();

                    foreach ($events as $date => $items) {

                        foreach ($items as $item) {

                            $this->info(\var_dump($item));

                            if (array_key_exists('title', $item)) {
                                $event_title = str_limit($item->title, 200);
                            } else {
                                $event_title = str_limit($item->summary, 200);
                            }

                            $with = [];
                            $with['collection'] = $settings['publish_to'];
                            $with['entry']['title'] = $event_title; // Add the title
                            $with['create'] = true;

                            // Item description
                            $with['entry'][Str::removeLeft($settings['pw_description'], '@ical:')] = $this->checkKey($settings, $item, 'description', 'description');

                            // UID
                            if ($item->uid) {
                                $with['entry']['pw_uid'] = $item->uid;
                            }

                            // Recurrence
                            if ($item->recurrence) {
                                $with['entry']['pw_recurring'] = $item->recurrence;
                            }

                            // Location
                            $with['entry'][Str::removeLeft($settings['pw_location'], '@ical:')] = $this->checkKey($settings, $item, 'location', 'location');

                            // Status
                            $with['entry'][Str::removeLeft($settings['pw_status'], '@ical:')] = $this->checkKey($settings, $item, 'status', 'status');

                            // Created
                            $with['entry'][Str::removeLeft($settings['pw_created'], '@ical:')] = $this->checkKey($settings, $item, 'created', 'created');

                            // Updated
                            $with['entry'][Str::removeLeft($settings['pw_updated'], '@ical:')] = $this->checkKey($settings, $item, 'updated', 'updated');

                            // Startdate
                            $with['entry'][Str::removeLeft($settings['pw_start_date'], '@ical:')] = $this->checkKey($settings, $item, 'start_date', 'dateStart');

                            // Enddate
                            $with['entry'][Str::removeLeft($settings['pw_end_date'], '@ical:')] = $this->checkKey($settings, $item, 'end_date', 'dateEnd');

                            // // Allow addons to modify the entry.
                            // $with = $this->runBeforeCreateEvent(array($with));

                            if ($with['create'] == true) {

                                // Create an entry
                                if (Entry::slugExists(slugify($with['entry']['title']), $with['collection'])) {
                                    $this->info($event_title . " <fg=red>already exists</>");
                                } else {

                                    // Checks the status
                                    if ($settings['status'] == 'publish') {
                                        $this->info('Adding "' . $event_title . '"');

                                        Entry::create(slugify($with['entry']['title']))
                                            ->collection($with['collection'])
                                            ->with($with['entry'])
                                            ->date($with['entry']['pw_start_date'])
                                            ->save();
                                    } else {
                                        $this->info('Adding "' . $event_title . '" <fg=red>(draft)</>');

                                        Entry::create(slugify($with['entry']['title']))
                                            ->collection($with['collection'])
                                            ->published(false)
                                            ->with($with['entry'])
                                            ->date(date('Y-m-d'))
                                            ->save();
                                    }

                                    $i++;
                                }
                            }
                        }
                    }
                }

                if ($i == 0) {
                    $this->info("No new events.");
                } else {
                    $this->info("Update complete. I found " . $i . " new events and added them to " . $with['collection'] . ".");
                }
                $this->info("\n");
            }
        }
    }


    /**
     * Check if a key exists
     *
     * @return value
     */
    public function checkKey($settings, $item, $name, $key)
    {
        if (isset($settings['pw_' . $name]) && $item->$key) {
            if ($settings['pw_' . $name] != false) {
                return $item->$key;
            }
        }
    }


    /**
     * Run the `HappyDates:beforecreate` event.
     *
     * This allows the entry to be short-circuited right before it's being created.
     * Or, the entry may be modified. Lastly, an addon could just 'do something'
     * here without modifying/stopping the entry.
     *
     * Expects an array of event responses (multiple listeners can listen for the same event).
     *
     * @param  Entry $entry
     * @return array
     */
    private function runBeforeCreateEvent($entry)
    {
        $responses = $this->emitEvent('beforecreate', $entry);

        // Replace entry with the response
        if (!empty(reset($responses))) {
            foreach ($responses as $response) {
                // Ignore any non-arrays
                if (! is_array($response)) {
                    continue;
                }

                // If the event returned a response, we'll replace it with that.
                $entry = $response;
            }
        } else {

            // Otherwise use the original entry, but it has to be reset.
            $entry = reset($entry);
        }

        return $entry;
    }
}
