<?php

namespace Evavel\Models\Traits;

use Evavel\Models\Relations\BelongsTo;
use Evavel\Models\Relations\BelongsToMany;
use Evavel\Models\Relations\HasMany;
use Evavel\Models\Relations\HasOne;


trait HasRelationships {

    protected $relations = [];

    public function belongsTo($related, $foreignKey = null, $ownerKey = null, $relation = null)
    {
        if (is_null($relation)){
            $relation = $this->guessBelongsToRelation();
        }

        // get new instance of the related model -> to calculate the ownerKey
        $instance = $this->newRelatedInstance($related);

        // calculate foreignKey if not provided
        if (is_null($foreignKey)){
            $foreignKey = $relation.'_'.$instance->getKeyName();
        }

        //  get the ownerKey if not provided
        $ownerKey = $ownerKey ? $ownerKey : $instance->getKeyName();

        // return new relation with the instance newQuery
        return new BelongsTo($instance::DB(), $this, $foreignKey, $ownerKey, $relation);
    }

	public function hasOne($related, $foreignKey = null, $localKey = null)
	{
		$instance = $this->newRelatedInstance($related);

		if (is_null($foreignKey)){
			$foreignKey = $this->getForeignKey();
		}

		if (is_null($localKey)){
			$localKey = $this->getKeyName();
		}

		return new HasOne($instance::DB(), $this, $foreignKey, $localKey);
	}

    public function hasMany($related, $foreignKey = null, $localKey = null)
    {
        $instance = $this->newRelatedInstance($related);

        if (is_null($foreignKey)){
            $foreignKey = $this->getForeignKey();
        }

        if (is_null($localKey)){
            $localKey = $this->getKeyName();
        }

        return new HasMany($instance::DB(), $this, $foreignKey, $localKey);
    }

    public function belongsToMany($related, $table, $foreignPivotKey = null, $relatedPivotKey = null,
                                  $parentKey = null, $relatedKey = null, $relation = null)
    {
        if (is_null($relation)){
            $relation = $this->guessBelongsToManyRelation();
        }

        $instance = $this->newRelatedInstance($related); // restaurant

        $foreignPivotKey = $foreignPivotKey ? $foreignPivotKey : $this->getForeignKey();
        $relatedPivotKey = $relatedPivotKey ? $relatedPivotKey : $instance->getForeignKey();

        $parentKey = $parentKey ? $parentKey : $this->getKeyName();
        $relatedKey = $relatedKey ? $relatedKey : $instance->getKeyName();

        /*ray($related);
        ray($relation);
        ray($table);
        ray($foreignPivotKey);
        ray($relatedPivotKey);
        ray($parentKey);
        ray($relatedKey);*/

        return new BelongsToMany($instance::DB(), $this, $table, $foreignPivotKey, $relatedPivotKey, $parentKey, $relatedKey, $relation);
    }

    protected function newRelatedInstance( $class_name )
    {
        return new $class_name();
    }

    protected function guessBelongsToRelation()
    {
        $calls = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 3);

        return $calls[2]['function'];
    }

    protected function guessBelongsToManyRelation()
    {
        $calls = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS); // Returns last calls
        foreach($calls as $trace){
            if (isset($trace['function']) && !in_array($trace['function'], ['guessBelongsToManyRelation', 'belongsToMany']))
            {
                return $trace['function'];
            }
        }
        return null;
    }
}
