<?php

namespace Alexr\Models\Traits;

use Alexr\Models\Combination;
use Alexr\Settings\ClosedTable;

trait CalculateBlockedTables {

	/**
	 * List of items with start,end,tables
	 * @param $date_string
	 *
	 * @return array
	 */
	function getBlockedTablesForDate($date_string)
	{
		$closedTables = ClosedTable::where('restaurant_id', $this->id)->first();
		if (!$closedTables) return [];

		// Comprobar si hay dia con mesas cerradas
		$closedTables = $closedTables->{$date_string};
		if (!$closedTables) return [];

		return $closedTables;
	}

	/**
	 * List of tables ids
	 * @param $date_string
	 * @param $time
	 *
	 * @return array
	 */
	function getBlockedTables($date_string, $time)
	{
		$tables_blocked = [];

		$closedTables = ClosedTable::where('restaurant_id', $this->id)->first();
		if (!$closedTables) return $tables_blocked;

		// Comprobar si hay dia con mesas cerradas
		$closedTables = $closedTables->{$date_string};
		if (!$closedTables) return $tables_blocked;

		// Check tables if inside interval
		foreach($closedTables as $item){
			$item_startTime = intval($item['start']);
			$item_endTime = intval($item['end']);
			if ($time >= $item_startTime && $time < $item_endTime)
			{
				$tables_blocked = array_merge($tables_blocked, $item['tables']);
			}
		}

		return $tables_blocked;
	}

	function getBlockedCombinations($date_string, $time)
	{
		return array_unique(array_merge(
			$this->getBlockedCombinations_becauseMarkedManually($date_string, $time),
			$this->getBlockedCombinations_becauseHasBlockedTable($date_string, $time)
		));
	}

	function getBlockedCombinations_becauseMarkedManually($date_string, $time)
	{
		$groups_blocked = [];

		$closedTables = ClosedTable::where('restaurant_id', $this->id)->first();
		if (!$closedTables) return $groups_blocked;

		// Comprobar si hay dia con grupos cerradas
		$closedTables = $closedTables->{$date_string};
		if (!$closedTables) return $groups_blocked;

		// Comprobar el intervalo
		foreach($closedTables as $item){
			$item_startTime = intval($item['start']);
			$item_endTime = intval($item['end']);
			if ($time >= $item_startTime && $time < $item_endTime)
			{
				$groups_blocked = array_merge($groups_blocked, $item['groups']);
			}
		}

		return $groups_blocked;
	}

	function getBlockedCombinations_becauseHasBlockedTable($date_string, $time)
	{
		$combinations = $this->getAllCombinationsList();
		$tables_blocked = $this->getBlockedTables($date_string, $time);

		$list_blocked = [];
		foreach($combinations as $combination) {
			$tables_ids_all = $combination['table_ids_all'];
			foreach($tables_ids_all as $table_id) {
				if (in_array($table_id, $tables_blocked)){
					$list_blocked[] = intval($combination['id']);
				}
			}
		}
		return array_unique($list_blocked);
	}

	public function getAllCombinationsList()
	{
		$combinations = Combination::where('restaurant_id', $this->id)
               ->orderBy('ordering', 'ASC')
               ->get()
               ->map(function($combination)
               {
                   $tables = $combination->listTables;
                   $table_ids_all = [];
                   foreach($tables as $table){
                       $table_ids_all[] = intval($table->id);

                   }
                   $combination->table_ids_all = $table_ids_all;
                   return $combination->attributes;
               })->toArray();

		return $combinations;
	}



	/**
	 * Check if table is bloqued
	 * @param $table_id
	 * @param $date_string
	 * @param $time
	 *
	 * @return bool
	 */
	function isTableBlocked($table_id, $date_string, $time)
	{
		$tables_blocked = $this->getBlockedTables($date_string, $time);

		return in_array($table_id, $tables_blocked);
	}

	/**
	 * Get all intervals where this table is blocked for a date
	 * @param $table_id
	 * @param $date_string
	 *
	 * @return array
	 */
	function getTablesTimesBlocked($table_id, $date_string)
	{
		$times = [];

		$closedTables = ClosedTable::where('restaurant_id', $this->id)->first();
		if (!$closedTables) return $times;

		$closedTables = $closedTables->{$date_string};
		if (!$closedTables) return $times;

		foreach($closedTables as $item) {
			if (in_array($table_id, $item['tables'])) {
				$times[] = [
					'start' => intval($item['start']),
					'end' => intval($item['end'])
				];
			}
		}

		return $times;
	}

	function hasDateBlockedTables($date_string)
	{
		$closedTables = ClosedTable::where('restaurant_id', $this->id)->first();
		if (!$closedTables) return false;

		$closedTables = $closedTables->{$date_string};
		if (!$closedTables) return false;

		return true;
	}

}
