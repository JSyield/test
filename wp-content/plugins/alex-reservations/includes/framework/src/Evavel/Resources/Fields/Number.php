<?php

namespace Evavel\Resources\Fields;

class Number extends Text
{
    public function __construct($name, $attribute = null)
    {
        parent::__construct($name, $attribute);

        $this->withMeta(['type' => 'number']);
    }

    public function min($min)
    {
        return $this->withMeta(['min' => $min]);
    }

    public function max($max)
    {
        return $this->withMeta(['max' => $max]);
    }

    public function step($step)
    {
        return $this->withMeta(['step' => $step]);
    }
}
