<?php

namespace Evavel\Resources\Fields;

class Timezone extends Select
{
    public function __construct($name, $attribute = null)
    {
        parent::__construct($name, $attribute);

        $this->options($this->timezones());
    }

    public function timezones()
    {
        $list = [];
        foreach(timezone_identifiers_list() as $tz){
            $list[$tz] = $tz;
        }
        return $list;
    }
}
