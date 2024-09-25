<?php

namespace Evavel\Http\Request\Traits;

use Evavel\Http\Request\FilterDecoder;

trait DecodesFilters
{
    public function filters()
    {
        $filterString = $this->filters;
        return (new FilterDecoder($filterString, $this->availableFilters()))->filters();
    }

    protected function availableFilters()
    {
        return $this->newResource()->availableFilters($this);
    }
}
