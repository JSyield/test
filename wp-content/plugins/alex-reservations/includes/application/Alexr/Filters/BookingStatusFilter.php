<?php

namespace Alexr\Filters;


use Alexr\Enums\BookingStatus;
use Evavel\Resources\Fields\Filter;
use Evavel\Http\Request\Request;

class BookingStatusFilter extends Filter
{

    public function name()
    {
        return __eva('Status');
    }

    public function apply(Request $request, $query, $value)
    {
        if (!empty($value)){
            $query->where('status', $value);
        }

        return $query;
    }

    public function options(Request $request)
    {
        return BookingStatus::listing();
    }
}
