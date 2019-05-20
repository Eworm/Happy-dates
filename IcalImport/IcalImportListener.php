<?php

namespace Statamic\Addons\IcalImport;

use Statamic\API\Nav;
use Statamic\Extend\Listener;

class IcalImportListener extends Listener
{
    /**
     * The events to be listened for, and the methods to call.
     *
     * @var array
     */
    public $events = [
        'cp.nav.created' => 'addNavItems',
        'cp.add_to_head' => 'addToHead',
        'IcalImport.beforecreate' => 'disableEntry'
    ];

    public function addNavItems($nav)
    {
        $syndication = Nav::item('Ical import')->route('addons.icalimport')->icon('calendar');
        $nav->addTo('tools', $syndication);
    }

    protected function getPlaceholder($key, $field, $data)
    {
        if (! $data) {
            return;
        }

        $vars = (new TagData)
            ->with(Settings::load()->get('defaults'))
            ->with($data->getWithCascade('anchorman', []))
            ->withCurrent($data)
            ->get();

        return array_get($vars, $key);
    }

    public function addToHead()
    {
        $assetContainer = $this->getConfig('asset_container');
        $suggestions = json_encode((new FieldSuggestions)->suggestions());
        return "<script>var IcalImport = { assetContainer: '{$assetContainer}', fieldSuggestions: {$suggestions} };</script>";
    }
}
