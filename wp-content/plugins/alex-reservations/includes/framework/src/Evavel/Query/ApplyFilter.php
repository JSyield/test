<?php

namespace Evavel\Query;

use Evavel\Http\Request\Request;

class ApplyFilter
{
    public $filter;
    public $value;

    public function __construct($filter, $value)
    {
        $this->value = $value;
        $this->filter = $filter;
    }

    public function __invoke(Request $request, $query)
    {
        $this->filter->apply(
            $request, $query, $this->value
        );

        return $query;
    }
}
