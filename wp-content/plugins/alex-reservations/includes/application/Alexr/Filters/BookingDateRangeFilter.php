<?php

namespace Alexr\Filters;


use Evavel\Http\Request\Request;
use Evavel\Resources\Fields\DateRangeFilter;

class BookingDateRangeFilter extends DateRangeFilter
{
    public function name()
    {
        return __eva('Date range');
    }

    public function apply(Request $request, $query, $value)
    {

        if(!is_array($value)) return $query;

        $from_date = $value['from'];
        $to_date = $value['to'];

        if (!empty($from_date)){
            $query->where('date', '>=', $from_date.' 00:00:00');
        }

        if (!empty($to_date)){
            $query->where('date', '<=', $to_date.' 23:59:59');
        }

        return $query;
    }

    public function currentValue()
    {
        return ['from' => '', 'to' => ''];
        //return ['from' => '2022-04-01', 'to' => '2022-04-06'];
    }

}
