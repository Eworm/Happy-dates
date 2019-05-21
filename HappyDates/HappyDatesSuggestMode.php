<?php namespace Statamic\Addons\HappyDates;

use Statamic\API\Taxonomy;
use Statamic\Addons\Suggest\Modes\AbstractMode;

class HappyDatesSuggestMode extends AbstractMode
{
    public function suggestions()
    {
        $taxonomies = Taxonomy::handles();
        $taxonomies_suggest = [];

        foreach ($taxonomies as $tax) {
            $taxonomies_suggest[] = [
                'value' => $tax,
                'text' => $tax
            ];
        }
        return $taxonomies_suggest;

    }
}
