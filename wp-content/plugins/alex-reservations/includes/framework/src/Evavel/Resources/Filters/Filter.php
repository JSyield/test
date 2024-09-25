<?php

namespace Evavel\Resources\Fields;

use Evavel\Http\Request\Request;
use Evavel\Interfaces\ToJsonSerialize;
use Evavel\Resources\Traits\Metable;
use Evavel\Support\Str;

abstract class Filter implements ToJsonSerialize
{
    use Metable;

    public $name;

    public $component = 'select-filter';

    abstract public function apply(Request $request, $query, $value);

    abstract public function options(Request $request);

    public function component()
    {
        return $this->component;
    }

    public function name()
    {
        return $this->name ?: Str::humanize($this);
    }

    public function key()
    {
        return get_class($this);
    }

    public function currentValue()
    {
        return '';
    }

    public function toJsonSerialize()
    {
        $request = evavel_make('request');

        return array_merge([
            'class' => $this->key(),
            'name' => $this->name(),
            'component' => $this->component(),
            'options' => evavel_collect($this->options($request))
                ->map(function($value, $key){
                    return is_array($value) ? ($value + ['value' => $key]) : ['name' => $value, 'value' => $key];
                })->values()->all(),
            'currentValue' => $this->currentValue() ?: ''
            ],
            $this->meta()
        );
    }

}
