<?php

namespace Evavel\Resources\Fields;

use Evavel\Http\Request\Request;

abstract class DateFilter extends Filter
{
    public $component = 'date-filter';

    public function firstDayOfWeek($day)
    {
        return $this->withMeta([__FUNCTION__ => $day]);
    }

    public function currentValue()
    {
        return '';
    }

    public function options(Request $request){}
}
