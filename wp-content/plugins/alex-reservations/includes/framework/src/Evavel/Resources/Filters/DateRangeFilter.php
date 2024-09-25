<?php

namespace Evavel\Resources\Fields;

use Evavel\Http\Request\Request;

abstract class DateRangeFilter extends Filter
{
    public $component = 'date-range-filter';

    public function options(Request $request){}
}
