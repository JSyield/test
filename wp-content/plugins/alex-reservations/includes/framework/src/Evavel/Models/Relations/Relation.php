<?php

namespace Evavel\Models\Relations;

abstract class Relation
{
    public $query;
    public $child;
    public $parent;
    public $foreignKey;

    abstract public function getResults();
}
