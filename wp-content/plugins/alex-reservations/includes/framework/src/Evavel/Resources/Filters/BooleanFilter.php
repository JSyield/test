<?php

namespace Evavel\Resources\Fields;

abstract class BooleanFilter extends Filter
{
    public $component = 'boolean-filter';

    public function currentValue()
    {
        $request = evavel_make('request');

        return evavel_collect($this->options($request))
            ->values()
            ->mapWithKeys(function($option){
                return [$option => false];
            })->all();
    }
}
