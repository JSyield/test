<?php

namespace Evavel\Resources\Traits;

use Evavel\Http\Request\Request;

trait ResolvesFilters
{
    public function availableFilters(Request $request)
    {
        return $this->resolveFilters($request)->values()->all();
        //proxy ->filter->authorizedToSee($request)->values();
    }

    public function resolveFilters(Request $request)
    {
        return evavel_collect(array_values($this->filters($request)));
    }

    public function filters(Request $request)
    {
        return [];
    }
}
