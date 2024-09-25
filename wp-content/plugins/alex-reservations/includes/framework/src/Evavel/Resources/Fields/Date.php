<?php

namespace Evavel\Resources\Fields;

class Date extends Field
{
    public $component = 'date-field';

    protected $dateFormat = 'YYYY-MM-DD';

    public function __construct($name, $attribute = null)
    {
        parent::__construct($name, $attribute);
        $this->format($this->dateFormat);
    }

    public function format($format)
    {
        return $this->withMeta([__FUNCTION__ => $format]);
    }
}
