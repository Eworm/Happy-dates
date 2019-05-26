<?php

namespace Statamic\Addons\HappyDates\Commands;

use Illuminate\Support\Facades\Storage;
use Statamic\API\Entry;
use Statamic\API\YAML;
use Statamic\API\Parse;
use Statamic\Extend\Command;

class DeleteEntriesCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'happydates:delete-entries
                            {feed=feed : The feed title}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Deletes all feed entries';

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

        $feed_name = $this->argument('feed');

        if ($this->storage->getYAML($feed_name) != null) {

            $data = $this->storage->getYAML($feed_name);
            $entries = Entry::whereCollection($data['publish_to']);

            foreach ($entries as $entry) {

                if ($entry->get('feed_title') == $feed_name) {
                    $entry->delete();
                }

            }
            $this->info('I finished deleting all entries made by ' . $feed_name);

        } else {

            $this->info('There is no feed with that name');

        }
    }
}
