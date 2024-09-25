<?php

namespace settings;

use Evavel\Enums\Context;

class Setting
{
    public $model;
    public $context;
    public $value;
    public $component = 'setting-setting';

    public $panel;
    public $panel_attribute;

    public function __construct($name, $attribute = null)
    {
        $this->name = $name;
        $this->attribute = $attribute ? $attribute : str_replace(' ', '_', strtolower($name));
    }

    public static function make(...$arguments)
    {
        return new static(...$arguments);
    }

    protected function resolveSettingAttribute()
    {
        return $this->attribute;

        /*$key = $this->attribute;
        if ($this->panel_attribute){
            $key = $this->panel_attribute.'_'.$key;
        }
        return $key;*/
    }

    protected function resolveSettingValue($model)
    {
        $key = $this->resolveSettingAttribute();
        $value =  $model->$key;
        $value_decode = json_decode($value, true);

        return $value_decode ? $value_decode : $value;
    }

    public function toJson($model, $context = Context::UPDATE)
    {
        return [
            'label' => $this->name,
            'attribute' => $this->resolveSettingAttribute(),
            'component' => $this->component,
            'panel' => $this->panel,
            'panel_attribute' => $this->panel_attribute,
            'value' => $this->resolveSettingValue($model)
        ];
    }

}
