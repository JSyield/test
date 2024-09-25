<?php

namespace Evavel\Models\Traits;

use Evavel\Query\Query;

trait InteractsPivotTable
{
    public function parseIds($value)
    {
        return (array) $value;
    }

    public function attach($id, $touch = true)
    {
        $rows = [];
        foreach($this->parseIds($id) as $id){
            $rows[] = [
                $this->foreignPivotKey => $this->child->id,
                $this->relatedPivotKey => $id
            ];
        }

        Query::table($this->table)->insert($rows);

        if ($touch){
            $this->child->touch();
        }
    }

    public function detach($ids = null, $touch = true)
    {
        $results = Query::table($this->table)
            ->where($this->foreignPivotKey, $this->child->id)
            ->whereIn($this->relatedPivotKey, $this->parseIds($ids))
            ->delete();

        if ($touch){
            $this->child->touch();
        }

        return $results;
    }

	protected function getTheCleanIds($ids)
	{
		$clean_ids = [];
		foreach($ids as $key => $value) {
			if (is_array($value)) {
				$clean_ids[] = $key;
			} else {
				$clean_ids[] = $value;
			}
		}
		return $clean_ids;
	}

	// Esto sincroniza los ids pero no el pivot
    public function sync($ids, $detaching = true)
    {
		// ids puede ser con pivot o sin pivot
	    // sin pivot: [306, 307]
	    // con pivot: [306 => '1,2', 307]

	    $clean_ids = $this->getTheCleanIds($ids);
		//ray($clean_ids);

        // Get all
        // Detach those not in the list
        // Attach the new ones
        $changes = ['attached' => [], 'detached' => [], 'updated' => []];

		// Listado de IDS actuales
        $current = $this->getCurrentlyAttachedPivots();



		// Attach o detach IDS
	    //--------------------------------------
		// Los ids nuevos
        //$records = $this->parseIds($ids);
	    $records = $this->parseIds($clean_ids);

        $detach = array_diff($current, $records);

        if($detaching && count($detach) > 0){
            $this->detach($detach);
            $changes['detached'] = array_values($detach);
        }

        $changes = array_merge(
            $changes, $this->attachNew($records, $current, false)
        );

        if (count($changes['detached']) || count($changes['attached'])){
            $this->child->touch();
        }



		// Tiene pivot columns - rellenarlos
	    //--------------------------------------
		if (!empty($this->pivotColumns)) {
			$ids_with_pivots = [];
			foreach($ids as $key => $value) {
				if (is_array($value)) {
					$ids_with_pivots[$key] = $value;
				} else {
					$ids_with_pivots[$value] = [];
				}
			}
			foreach($ids_with_pivots as $related_id => $pivots)
			{
				$values = [];
				foreach($this->pivotColumns as $pivot) {
					$values[$pivot] = $pivots[$pivot] ?? '';
				}
				Query::table($this->table)
					->where($this->foreignPivotKey, $this->child->id)
					->where($this->relatedPivotKey, $related_id)
					->update($values);
			}
		}

        return $changes;
    }

    protected function attachNew(array $records, array $current, $touch = true)
    {
        $changes = ['attached' => []];

        foreach($records as $id){
            if (!in_array($id, $current)){
                $this->attach([$id], $touch);
                $changes['attached'][] = $id;
            }
        }

        return $changes;
    }

    public function syncWithoutDetaching($ids)
    {
        return $this->sync($ids, false);
    }


    public function getCurrentlyAttachedPivots()
    {
        return evavel_collect(
            Query::table($this->table)
            ->where($this->foreignPivotKey, $this->child->id)
            ->toArray()
            ->get()
        )->pluck($this->relatedPivotKey);
    }

	public function withPivot($columns) {
		$this->pivotColumns = array_merge($this->pivotColumns, $columns);
		return $this;
	}
}
