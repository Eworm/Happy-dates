<?php

namespace Statamic\Addons\IcalImport\Controllers;

use Illuminate\Support\Facades\Storage;
use Statamic\Addons\IcalImport\iCal;
use Statamic\Console\Please;
use Statamic\API\Path;
use Statamic\API\File;
use Statamic\API\YAML;
use Statamic\API\Parse;
use Statamic\Addons\IcalImport\Settings;
use Statamic\API\Fieldset;
use Illuminate\Http\Request;
use Statamic\Addons\IcalImport\TranslatesFieldsets;
use Statamic\CP\Publish\ProcessesFields;
use Statamic\CP\Publish\ValidationBuilder;
use Statamic\CP\Publish\PreloadsSuggestions;

class IcalImportController extends Controller
{
    use TranslatesFieldsets;

    /**
     * Maps to your route definition in routes.yaml
     *
     * @return mixed
     */
    public function index()
    {
        $events_storage  = Storage::files('/site/storage/addons/IcalImport');
        $events          = [];

        if (!$events_storage) {
            return redirect()->route('addons.icalimport.create');
        }

        foreach ($events_storage as $event) {
            $rem    = str_replace('site/storage/addons/IcalImport/', '', $event);

            $ignore = array( 'cgi-bin', '.', '..','._' );
            if (!in_array($rem, $ignore) and substr($rem, 0, 1) != '.') {
                $info = $this->storage->getYaml($rem);

                if (isset($info['updated'])) {
                    $timediff = (time() - $info['updated']) / 60;
                    $updated = number_format($timediff, 0);

                    if ($updated == 1) {
                        $updated = $updated . ' minute ago';
                    } else {
                        $updated = $updated . ' minutes ago';
                    }
                } else {
                    $updated = 'No updates yet';
                }

                $icals[] = (object) [
                     'enabled'   => $info['enabled'],
                     'collection'=> $info['publish_to'],
                     'name'      => slugify($info['events_title']),
                     'title'     => $info['events_title'],
                     'updated'   => $updated,
                     'url'       => $info['url']
                 ];
            }
        }

        return $this->view('index', [
             'icals' => $icals
         ]);
    }


    /**
     * Maps to the edit page
     *
     * @return mixed
     */
    public function edit(Request $request)
    {
        $fieldset  = $this->fieldset('edit');

        $data = $this->preProcessWithBlankFields(
             $fieldset,
             $this->storage->getYaml($request->event)
         );

        return $this->view('edit', [
             'data'         => $data,
             'fieldset'     => $fieldset->toPublishArray(),
             'submitUrl'    => route('addons.icalimport.update'),
             'suggestions'  => $this->getSuggestions($fieldset),
             'title'        => $data['events_title'],
         ]);
    }


    /**
     * Maps to the create page
     *
     * @return mixed
     */
    public function create()
    {
        $fieldset  = $this->fieldset('edit');

        $data = $this->preProcessWithBlankFields(
             $fieldset,
             YAML::parse(File::get($this->getDirectory() . '/defaults.yaml'))
         );

        return $this->view('create', [
             'data'         => $data,
             'fieldset'     => $fieldset->toPublishArray(),
             'submitUrl'    => route('addons.icalimport.store'),
             'title'        => 'Create ical',
         ]);
    }

    /**
     * Stores a new ical
     *
     * @return mixed
     */
    public function store(Request $request)
    {
        $event = new iCal($request->fields['url']);
        $events_title = slugify($event->title);

        if ($event) {
            $data = $this->processFields($this->fieldset('edit'), $request->fields);
            $data['events_title'] = $event->title;
            $this->storage->putYAML($events_title, $data);

            return [
                 'success' => true,
                 'message' => 'Ical created successfully.',
                 'event'    => $events_title
             ];
        }
    }


    /**
     * Saves data when updating a ical
     *
     * @return mixed
     */
    public function update(Request $request)
    {
        $data = $this->processFields($this->fieldset('edit'), $request->fields);
        $this->storage->putYAML(slugify($data['events_title']), $data);

        return [
             'success' => true,
             'message' => 'Ical updated successfully.',
             'event'    => $request->event
         ];
    }


    /**
     * Maps to your route definition in routes.yaml
     *
     * @return mixed
     */
    public function destroy(Request $request)
    {
        Storage::delete('site/storage/addons/icalimport/' . $request->event . '.yaml');

        return [
             'success' => true,
             'message' => 'Ical deleted successfully'
         ];
    }


    protected function fieldset($fieldset)
    {
        return $this->translateFieldset(Fieldset::create(
             $fieldset,
             YAML::parse(File::get($this->getDirectory() . '/resources/fieldsets/' . $fieldset . '.yaml'))
         ));
    }

    private function prepareData($data)
    {
        return $this->preProcessWithBlankFields(Fieldset::get('post'), $data);
    }
}
