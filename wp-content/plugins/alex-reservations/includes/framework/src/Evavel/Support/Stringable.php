<?php

namespace Evavel\Support;

use Evavel\Interfaces\ToJsonSerialize;

class Stringable implements ToJsonSerialize
{
    protected $value;

    public function __construct($value = '')
    {
        $this->value = (string) $value;
    }

    public function __get($key)
    {
        return $this->{$key}();
    }

    public function __toString()
    {
        return (string) $this->value;
    }

    public function toJsonSerialize()
    {
        return $this->__toString();
    }
}
