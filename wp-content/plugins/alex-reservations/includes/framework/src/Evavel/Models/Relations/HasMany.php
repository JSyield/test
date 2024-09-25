<?php

namespace Evavel\Models\Relations;

use Evavel\Query\Query;

class HasMany extends Relation
{
    public $ownerKey;
    public $localKey;

    public function __construct(Query $query, \Evavel\Models\Model $parent, $foreignKey, $localKey)
    {
        $this->query = $query;
        $this->parent = $parent;
        $this->foreignKey = $foreignKey;
        $this->localKey = $localKey;
    }

    public function buildQuery($query)
    {
        return $query->where($this->foreignKey, $this->parent->id);
    }

    public function getResults()
    {
        return $this->buildQuery($this->query)->get();
        //return $this->query->where($this->foreignKey, $this->parent->id)->get();

       /*
        return [
            $this->query,
            $this->parent,
            $this->foreignKey,
            $this->localKey
        ];
       */
    }
}
