<?php

namespace Alexr\Filters;

use Evavel\Http\Request\Request;
use Evavel\Resources\Fields\DateFilter;

class BookingDateFilter extends DateFilter
{

    public function name()
    {
        return __eva('Booking date');
    }

    public function apply(Request $request, $query, $value)
    {
        if (!empty($value)){
            $query->where('date', 'like', $value);
        }

        return $query;
    }

    public function currentValue()
    {
        return '';
        //return '2022-04-05';
    }
}
