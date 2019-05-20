<?php namespace Statamic\Addons\HappyDates\SuggestModes;

use Statamic\API\AssetContainer;
use Statamic\Addons\Suggest\Modes\AbstractMode;

class AssetsSuggestMode extends AbstractMode
{
    public function suggestions()
    {

        $containers = AssetContainer::all();
        $containersArray = $containers->toArray();
        $containers_suggest = [];

        foreach ($containersArray as $container) {
            $containers_suggest[] = [
                'value' => $container->data()['path'],
                'text' => $container->data()['title']
            ];
        }
        return $containers_suggest;

    }
}
