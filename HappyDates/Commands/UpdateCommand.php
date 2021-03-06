<?php

namespace Statamic\Addons\HappyDates\Commands;

use Statamic\API\Str;
use Statamic\Addons\HappyDates\iCal;
use Illuminate\Support\Facades\Storage;
use Statamic\API\Entry;
use Statamic\API\Term;
use Statamic\API\Taxonomy;
use Statamic\API\File;
use Statamic\Extend\Command;
use Carbon\Carbon;
use Statamic\Data\Data;

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
    protected $description = 'Updates all feeds';

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
        $feeds_storage  = Storage::files('/site/storage/addons/HappyDates');

        foreach ($feeds_storage as $event) {
            $st_event = str_replace('site/storage/addons/HappyDates/', '', $event);
            $ignore = array( 'cgi-bin', '.', '..','._' );

            if (!in_array($st_event, $ignore) and substr($st_event, 0, 1) != '.') {
                $settings = $this->storage->getYaml($st_event);
                $settings_title = $settings['title'];
                $settings_url = $settings['url'];
                $enabled = $settings['enabled'];

                // Add the last updated time to the feed info
                $settings['updated'] = time();
                $this->storage->putYAML($st_event, $settings);

                $this->info('Updating ' . $settings['title']);
                $i = 0;

                // Saves custom terms to the chosen taxonomy
                if (isset($settings['custom_terms']) && isset($settings['custom_taxonomies'])) {
                    $taxonomy = $settings['custom_taxonomies'];
                    $this->save_custom_terms($settings['custom_terms'], $taxonomy);
                }

                if ($enabled == true) {
                    $ical = new iCal($settings_url, slugify($settings_title));
                    $events = $ical->eventsByDate();
                    $timezone = $ical->timezone;

                    foreach ($events as $date => $items) {
                        foreach ($items as $item) {

                            if (array_key_exists('title', $item)) {
                                $event_title = str_limit($item['event']->title, 200);
                            } else {
                                $event_title = str_limit($item['event']->summary, 200);
                            }

                            $with = [];
                            $with['collection'] = $settings['publish_to'];
                            $with['entry']['feed_title'] = slugify($settings_title); // Add feed title
                            $with['entry']['title'] = $event_title; // Add the title
                            $with['entry']['pw_timezone'] = $timezone; // Add the timezone
                            $with['create'] = true;

                            // See if there's a sequence
                            if (\array_key_exists('sequence', $item['event'])) {
                                $num = $item['event']->sequence;
                                $int = (int)$num;
                                $with['entry']['sequence'] = $int; // Set the sequence
                            } else {
                                $with['entry']['sequence'] = 0; // Set the sequence to 0
                            }

                            // Item description
                            $with['entry'][Str::removeLeft($settings['pw_description'], '@ical:')] = $this->checkKey($settings, $item['event'], 'description', 'description');

                            // Check for recurrence and add the info
                            if ($item['event']->recurrence != null) {
                                $with['entry']['pw_recurring'] = true;
                                if (\array_key_exists('freq', $item['event']->recurrence)) {
                                    $with['entry']['pw_recurring_frequency'] = $item['event']->recurrence->freq;
                                }
                                if (\array_key_exists('interval', $item['event']->recurrence)) {
                                    $with['entry']['pw_recurring_interval'] = $item['event']->recurrence->interval;
                                }
                                if (\array_key_exists('count', $item['event']->recurrence)) {
                                    $with['entry']['pw_recurring_count'] = $item['event']->recurrence->count;
                                    $with['entry']['pw_recurring_ends'] = 'after';
                                }
                                if (\array_key_exists('until', $item['event']->recurrence)) {
                                    $with['entry']['pw_recurring_count'] = $item['event']->recurrence->until;
                                    $with['entry']['pw_recurring_ends'] = 'on';
                                }
                                if (\array_key_exists('byday', $item['event']->recurrence)) {
                                    $byday = [];
                                    foreach ($item['event']->recurrence->byday as $day) {
                                        $byday[] = $day;
                                    }
                                    $with['entry']['pw_recurring_byday'] = $byday;
                                }
                            }

                            // Location
                            $with['entry'][Str::removeLeft($settings['pw_location'], '@ical:')] = $this->checkKey($settings, $item['event'], 'location', 'location');

                            // Status
                            $with['entry'][Str::removeLeft($settings['pw_status'], '@ical:')] = $this->checkKey($settings, $item['event'], 'status', 'status');

                            // Created
                            $with['entry'][Str::removeLeft($settings['pw_created'], '@ical:')] = $this->checkKey($settings, $item['event'], 'created', 'created');

                            // Updated
                            $with['entry'][Str::removeLeft($settings['pw_updated'], '@ical:')] = $this->checkKey($settings, $item['event'], 'updated', 'updated');

                            // Startdate
                            $with['entry'][Str::removeLeft($settings['pw_start_date'], '@ical:')] = $this->checkKey($settings, $item['event'], 'start_date', 'dateStart');

                            // Enddate
                            $with['entry'][Str::removeLeft($settings['pw_end_date'], '@ical:')] = $this->checkKey($settings, $item['event'], 'end_date', 'dateEnd');

                            // Custom terms
                            if ($settings['enable_taxonomies'] == true) {
                                if (isset($settings['custom_terms']) && isset($settings['custom_taxonomies'])) {
                                    $with['entry'][$taxonomy] = $this->add_custom_terms($settings['custom_terms']);
                                }
                            }

                            // Allow addons to modify the entry.
                            $with = $this->runBeforeCreateEvent(array($with));

                            if ($with['create'] == true) {

                                // Create an entry
                                $entry_title = slugify($with['entry']['title']);
                                $entry_collection = $with['collection'];
                                $entry_uid = $this->clean($item['event']->uid);

                                // Find a file by id and delete it if there are changes
                                $file = Entry::find($entry_uid);
                                if ($file) {

                                    if (($file->get('sequence') < $with['entry']['sequence'])) {
                                        $this->info('Deleting "' . $event_title . '"');
                                        $file->delete();
                                    } else if (($file->get('pw_updated') < $with['entry']['pw_updated'])) {
                                        $this->info('Deleting "' . $event_title . '"');
                                        $file->delete();
                                    }

                                }

                                if (Entry::slugExists($entry_title, $entry_collection)) {

                                    $this->info($event_title . " <fg=red>already exists</>");

                                } else {

                                    // Checks the status
                                    if ($settings['status'] == 'publish') {
                                        $this->info('Adding "' . $event_title . '"');

                                        Entry::create($entry_title)
                                            ->collection($entry_collection)
                                            ->with($with['entry'])
                                            ->date($with['entry']['pw_start_date'])
                                            ->id($entry_uid)
                                            ->save();
                                    } else {
                                        $this->info('Adding "' . $event_title . '" <fg=red>(draft)</>');

                                        Entry::create($entry_title)
                                            ->collection($entry_collection)
                                            ->published(false)
                                            ->with($with['entry'])
                                            ->date(date('Y-m-d'))
                                            ->id($entry_uid)
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


    /**
     * Saves custom terms
     *
     * @return info
     */
    private function save_custom_terms($tags, $taxonomy)
    {
        foreach ($tags as $term) {

            if (!Term::slugExists(slugify($term), $taxonomy)) {

                $this->info('Adding "' . $term . '" to "' . $taxonomy . '".');
                Term::create(slugify($term))
                    ->taxonomy($taxonomy)
                    ->save();

            } else {

                $this->info('"' . $term . '" <fg=red>already exists</>');

            }
        }
    }


    /**
     * Adds custom terms to an entry
     *
     * @return array
     */
    private function add_custom_terms($tags)
    {
        $newtags = [];
        foreach ($tags as $term) {
            array_push($newtags, $term);
        }
        return $newtags;
    }


    /**
     * Cleans a string
     *
     * @return string
     */
    private function clean($string)
    {
        $string = str_replace(' ', '-', $string); // Replaces all spaces with hyphens.
        return preg_replace('/[^A-Za-z0-9]/', '', $string); // Removes special chars.
    }
}
