<?php

namespace Statamic\Addons\HappyDates\Fieldtypes;

use Statamic\API\Str;
use Statamic\Extend\Fieldtype;
use Statamic\CP\FieldtypeFactory;

class SourceFieldtype extends Fieldtype
{
    public $selectable = false;

    public function preProcess($data)
    {
        if (is_string($data) && Str::startsWith($data, '@ical:')) {
            return ['source' => 'field', 'value' => explode('@ical:', $data)[1]];
        }

        if ($data === false && $this->getFieldConfig('disableable') === true) {
            return ['source' => 'disable', 'value' => null];
        }

        return ['source' => 'custom', 'value' => $this->fieldtype()->preProcess($data)];
    }

    public function process($data)
    {
        if ($data['source'] === 'field') {
            return '@ical:' . $data['value'];
        }

        if ($data['source'] === 'disable') {
            return false;
        }

        return $this->fieldtype()->process($data['value']);
    }

    protected function fieldtype()
    {
        $config = $this->getFieldConfig('field');

        return FieldtypeFactory::create(array_get($config, 'type'), $config);
    }
}
