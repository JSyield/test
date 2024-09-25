<?php

namespace Evavel\Http\Request;

use Evavel\Query\ApplyFilter;

class FilterDecoder
{
    protected $filterString;

    protected $availableFilters;

    public function __construct($filterString, $availableFilters = null)
    {
        $this->filterString = $filterString;
        $this->availableFilters = evavel_collect($availableFilters);
    }

    public function filters()
    {
        if (empty($filters = $this->decodeFromBase64String())) {
            return [];
        }

        return evavel_collect($filters)->map(function($filter){
            $class = key($filter);
            $value = $filter[$class];

           $matchingFilter = $this->availableFilters->first(function($availableFilter) use ($class) {
               return $class === $availableFilter->key();
           });

            if ($matchingFilter) {
                return ['filter' => $matchingFilter, 'value' => $value];
            }
        })
            ->filter()
            ->reject(function ($filter) {
                if (is_array($filter['value'])) {
                    return count($filter['value']) < 1;
                } elseif (is_string($filter['value'])) {
                    return trim($filter['value']) === '';
                }

                return is_null($filter['value']);
            })
            ->map(function($filter){
                return new ApplyFilter($filter['filter'], $filter['value']);
            })
            ->values();

    }

    public function decodeFromBase64String()
    {
        if (empty($this->filterString)) {
            return [];
        }

        $filters = json_decode(base64_decode($this->filterString), true);

        return is_array($filters) ? $filters : [];
    }


}
