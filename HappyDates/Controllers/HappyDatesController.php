<?php

namespace Statamic\Addons\HappyDates\Controllers;

use Illuminate\Support\Facades\Storage;
use Statamic\Console\Please;
use Statamic\API\Path;
use Statamic\API\Entry;
use Statamic\API\File;
use Statamic\API\YAML;
use Statamic\API\Parse;
use Statamic\Addons\HappyDates\Settings;
use Statamic\API\Fieldset;
use Illuminate\Http\Request;
use Statamic\Addons\HappyDates\TranslatesFieldsets;
use Statamic\CP\Publish\ProcessesFields;
use Statamic\CP\Publish\ValidationBuilder;
use Statamic\CP\Publish\PreloadsSuggestions;

class HappyDatesController extends Controller
{
    use TranslatesFieldsets;

    /**
     * Maps to your route definition in routes.yaml
     *
     * @return mixed
     */
    public function index()
    {
        $icals_storage  = Storage::files('/site/storage/addons/HappyDates');
        $icals          = [];

        if (!$icals_storage) {
            return redirect()->route('addons.happydates.create');
        }

        foreach ($icals_storage as $ical) {
            $rem    = str_replace('site/storage/addons/HappyDates/', '', $ical);

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
                     'name'      => slugify($info['title']),
                     'title'     => $info['title'],
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
             'submitUrl'    => route('addons.happydates.update'),
             'suggestions'  => $this->getSuggestions($fieldset),
             'title'        => $data['title'],
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
             'submitUrl'    => route('addons.happydates.store'),
             'title'        => 'Create ical',
         ]);
    }


    /**
     * Maps to the refresh all command
     *
     * @return mixed
     */
    public function refreshAll()
    {
        Please::call('happydates:refresh');
        return redirect()->route('addons.happydates');
    }

    /**
     * Stores a new ical
     *
     * @return mixed
     */
    public function store(Request $request)
    {
        $ical_title = slugify($request->fields['title']);

        $data = $this->processFields($this->fieldset('edit'), $request->fields);
        $data['title'] = $request->fields['title'];
        $this->storage->putYAML($ical_title, $data);

        return [
            'success' => true,
            'message' => 'Ical created successfully.',
            'event'    => $ical_title
        ];
    }


    /**
     * Saves data when updating a ical
     *
     * @return mixed
     */
    public function update(Request $request)
    {
        $data = $this->processFields($this->fieldset('edit'), $request->fields);
        $this->storage->putYAML(slugify($data['title']), $data);

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
        Storage::delete('site/storage/addons/HappyDates/' . $request->feed . '.yaml');

        return [
            'success' => true,
            'message' => 'Ical deleted successfully'
        ];
    }


    /**
     * Maps to your route definition in routes.yaml
     *
     * @return mixed
     */
    public function destroy_entries(Request $request)
    {

        Please::call('happydates:delete_entries', $request->feed);
        return redirect()->route('addons.happydates');

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
