<?php

namespace Evavel\Models\Relations;

use Evavel\Query\Query;
use \Evavel\Models\Traits\InteractsPivotTable;

class BelongsToMany extends Relation
{
    use InteractsPivotTable;

    public $table;
    public $foreignPivotKey;
    public $relatedPivotKey;
    public $parentKey;
    public $relatedKey;
    public $relationName;

	// Para los campos de la tabla relacionada
	public array $pivotColumns = [];
	protected $accessor = 'pivot'; // pivot_booking_id, pivot_table_id, pivot_seat

	public $relationColumns;

    public function __construct(Query $query, \Evavel\Models\Model $child, $table,
        $foreignPivotKey, $relatedPivotKey,
        $parentKey, $relatedKey, $relationName = null)
    {
        $this->query = $query;
        $this->child = $child;
        $this->table = $table; // restaurant_user
        $this->foreignPivotKey = $foreignPivotKey; // user_id
        $this->relatedPivotKey = $relatedPivotKey; // restaurant_id
        $this->parentKey = $parentKey; // id (users.id)
        $this->relatedKey = $relatedKey; // id (restaurants.id)
        $this->relationName = $relationName; // restaurants
    }

	public function addRelationColumns( $relation_columns )
	{
		$this->relationColumns = $relation_columns;
	}

    public function tableColumns($table)
    {
        static $columns = [];

        if (!isset($columns[$table])){
            $columns[$table] = Query::table($table)->getColumns();
        }
        return $columns[$table];
    }

    public function buildQuery($query)
    {
		$columns = $this->tableColumns($this->relationName);

		if ($this->relationColumns != null) {
			foreach($this->relationColumns as $column){
				$columns[] = $this->table.'.'.$column;
			}
		}

		// Añadir pivot columns
	    $this->pivotColumns[] = 'id';
	    $this->pivotColumns[] = $this->relatedPivotKey;
		$this->pivotColumns[] = $this->foreignPivotKey;
		//foreach ($this->pivotColumns as $pivot_column) {
		//	$columns[] = $this->table.'.'.$pivot_column.'` AS `'.$this->accessor.'_'.$pivot_column;
		//}


        // Extract specific columns and skip relation table columns
        return $query
            ->select($columns)
	        ->pivot($this->pivotColumns, $this->accessor)
            ->join($this->table, "{$this->relationName}.{$this->relatedKey}", '=', "{$this->table}.{$this->relatedPivotKey}")
            ->where("{$this->table}.{$this->foreignPivotKey}", $this->child->id);
    }

    public function getResults()
    {
        return $this->buildQuery($this->query)->get();

        //return $this->query->join($this->table, $this->relationName.'.'.$this->relatedKey, '=', $this->table.'.'.$this->relatedPivotKey)
        //    ->where($this->table.'.'.$this->foreignPivotKey, $this->child->id)->get();

        // Example
        // "select * from `restaurants` inner join `restaurant_user` on `restaurants`.`id` = `restaurant_user`.`restaurant_id` where `restaurant_user`.`user_id` = ?"
        //return SRR_Restaurant::DB()->join('restaurant_user', 'restaurants.id', '=', 'restaurant_user.restaurant_id')
        //    ->where('restaurant_user.user_id', $this->parent->id)->get();

    }

}
