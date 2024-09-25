<?php

namespace Evavel\Resources\Traits;

trait Searchable
{
    public $searchable = false;

    public function searchable($searchable = true)
    {
        $this->searchable = $searchable;

        return $this;
    }
}
