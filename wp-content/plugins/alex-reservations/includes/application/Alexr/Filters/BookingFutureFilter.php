<?php

namespace Alexr\Filters;


use Evavel\Http\Request\Request;
use Evavel\Resources\Fields\BooleanFilter;

class BookingFutureFilter extends BooleanFilter
{

    public function apply(Request $request, $query, $value)
    {
        // TODO: Implement apply() method.
        return $query;
    }

    public function options(Request $request)
    {
        // TODO: Implement options() method.
    }
}
