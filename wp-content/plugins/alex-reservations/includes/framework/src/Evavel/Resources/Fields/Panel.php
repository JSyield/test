<?php

namespace Evavel\Resources\Fields;

use Evavel\Interfaces\ToJsonSerialize;
use Evavel\Resources\Resource;

class Panel implements ToJsonSerialize
{
    // Use null for the ungrouped fields
    public $name;

    // Used for the panel null
    public $title;

    public $component = 'panel';
    public $items;

    public $showToolbar = false;
    public $helpText;

    public function __construct($name, $fields = [])
    {
        $this->name = $name;
        $this->items = $this->assignPanelToFields($fields);
        return $this;
    }

    public static function make(...$arguments)
    {
        return new static(...$arguments);
    }

    // Panel used for ungrouped fields. Is the first one in the Details view
    public static function makeDefault(Resource $resource)
    {
        $default_panel = new Panel(null);
        $default_panel->showToolbar = true;
        $default_panel->title = $resource->title();
        return $default_panel;
    }

    protected function assignPanelToFields($fields)
    {
        $list = [];
        foreach ($fields as $field) {
            $field->panel = $this->name;
            $list[] = $field;
        }
        return $list;
    }

    public function help($helpText)
    {
        $this->helpText = $helpText;
        return $this;
    }

    public function toJsonSerialize()
    {
        return [
            'name' => $this->name,
            'title' => $this->title,
            'component' => $this->component,
            'showToolbar' => $this->showToolbar,
            'helpText' => $this->helpText
        ];
    }

}
