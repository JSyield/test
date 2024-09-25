<?php

namespace Evavel\Models\Relations;

use Evavel\Query\Query;

class BelongsTo extends Relation
{
    protected $ownerKey;
    protected $relation;

    public function __construct(Query $query, \Evavel\Models\Model $child, $foreignKey, $ownerKey, $relation)
    {
        $this->query = $query;
        $this->child = $child;
        $this->foreignKey = $foreignKey;
        $this->ownerKey = $ownerKey;
        $this->relation = $relation;
    }

    public function getResults()
    {
        $foreignKey = $this->foreignKey;

        return $this->query
            ->where($this->ownerKey, $this->child->$foreignKey)
            ->first();

        /*return [
            $this->query,
            $this->child,
            $this->foreignKey,
            $this->ownerKey,
            $this->relation
        ];*/
    }
}
