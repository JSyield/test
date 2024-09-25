<?php

namespace settings;

class Panel
{
    public $name;
    public $component = 'setting-panel';
    public $attribute;
    public $items;

    public function __construct($name, $attribute, $fields = [])
    {
        $this->name = $name;
        $this->attribute = $attribute;
        $this->items = $this->assignPanelToFields($fields);
    }

    protected function assignPanelToFields($fields)
    {
        $list = [];
        foreach ($fields as $field) {
            $field->panel = $this->name;
            $field->panel_attribute = $this->attribute;
            $list[] = $field;
        }
        return $list;
    }
}
