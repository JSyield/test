<?php

namespace Evavel\Models\Relations;

use Evavel\Query\Query;

class HasOne extends Relation
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
		return $this->buildQuery($this->query)->first();
	}
}
