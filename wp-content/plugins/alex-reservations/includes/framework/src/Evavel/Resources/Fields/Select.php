<?php

namespace Evavel\Resources\Fields;

use Evavel\Http\Request\Request;
use Evavel\Resources\Traits\Searchable;

class Select extends Field
{
    use Searchable;

    public $component = 'select-field';

    public function options($options, $meta = 'options')
    {
        if (is_callable($options)){
            $options = $options();
        }

        $list = evavel_collect( isset($options) ? $options : [])
            ->map(function($label, $value){
                return [
                    'label' => $label,
                    'value' => $value
                ];
            })
            ->values()
            ->all();

        return $this->withMeta([$meta => $list]);
    }

    public function styles($options, $meta = 'options')
    {
        return $this->options($options, 'styles');
    }

    public function toJsonSerialize()
    {
        return array_merge([
            'searchable' => $this->searchable,
        ], parent::toJsonSerialize());
    }
}
