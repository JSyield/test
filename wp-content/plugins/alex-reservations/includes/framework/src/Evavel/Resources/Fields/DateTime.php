<?php

namespace Evavel\Resources\Fields;


class DateTime extends Field
{
    public $component = 'date-time-field';

    protected $dateFormat = 'YYYY-MM-DD HH:mm:ss';

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
