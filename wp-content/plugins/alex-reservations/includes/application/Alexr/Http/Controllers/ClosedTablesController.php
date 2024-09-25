<?php

namespace Alexr\Http\Controllers;

use Alexr\Models\Restaurant;
use Alexr\Settings\ClosedTable;
use Evavel\Http\Controllers\Controller;
use Evavel\Http\Request\Request;

class ClosedTablesController extends Controller
{
	public function forDate(Request $request)
	{
		$tenantId = $request->tenantId();
		if (!$tenantId) {
			return $this->response(['success' => false, 'error' => __eva('Tenant is not valid.')]);
		}

		$date = $request->date;

		$closedTables = ClosedTable::where(evavel_tenant_field(), $tenantId)->first();
		if ($closedTables) {
			$items = $closedTables->{$date};
		}
		if (!$items){
			$items = [];
		}

		return $this->response([
			'success' => true,
			'items' => $items,
		]);
	}

	public function saveForDate(Request $request)
	{
		$tenantId = $request->tenantId();
		if (!$tenantId) {
			return $this->response(['success' => false, 'error' => __eva('Tenant is not valid.')]);
		}

		$date = $request->date;
		$items = $request->items;

		$closedTables = ClosedTable::where(evavel_tenant_field(), $tenantId)->first();
		if (!$closedTables) {
			$closedTables = ClosedTable::create([
				evavel_tenant_field() => $tenantId
			]);
		}

		$closedTables->{$date} = json_decode($items, true);

		$meta_value = $closedTables->attributes['meta_value'];
		$closedTables->attributes['meta_value'] = $this->cleanMetaValue($meta_value);
		$closedTables->save();

		return $this->response(['success' => true]);
	}

	public function cleanMetaValue($meta_value) {
		$new_meta_value = [];
		foreach($meta_value as $date => $value) {
			if (!empty($value)){
				$new_meta_value[$date] = $value;
			}
		}
		return $new_meta_value;
	}

	public function getDatesWithTablesClosed(Request $request)
	{
		$tenantId = $request->tenantId();
		if (!$tenantId) {
			return $this->response(['success' => false, 'error' => __eva('Tenant is not valid.')]);
		}

		$range = $request->range;
		$first_date = substr($range, 0, 10);
		$last_date = substr($range, 11, 10);
		//ray($first_date.' - '.$last_date);

		// For each date check if it is in the range and if it has some table
		$closedTables = ClosedTable::where(evavel_tenant_field(), $tenantId)->first();
		$dates_with_closed_tables = [];
		if ($closedTables)
		{
			$meta_value = json_decode($closedTables->original['meta_value'], true);
			foreach($meta_value as $date => $items) {
				if (is_array($items) && count($items) > 0 && $date >= $first_date && $date <= $last_date) {
					$dates_with_closed_tables[] = $date;
				}
			}
		}

		$dates_with_closed_tables = array_values(array_unique($dates_with_closed_tables));

		return $this->response([
			'success' => true,
			'dates' => $dates_with_closed_tables
		]);
	}

	public function getTableTimesClosed(Request $request)
	{
		$tenantId = $request->tenantId();
		$table_id = $request->tableId;
		$date_string = $request->date;

		if (!$tenantId) {
			return $this->response(['success' => false, 'error' => __eva('Tenant is not valid.')]);
		}

		$restaurant = Restaurant::find($tenantId);
		$times = $restaurant->getTablesTimesBlocked($table_id, $date_string);

		return $this->response([
			'success' => true,
			'times' => $times
		]);
	}

	public function getStatuses(Request $request)
	{
		$tenantId = $request->tenantId();
		$date_string = $request->date;

		if (!$tenantId) {
			return $this->response(['success' => false, 'error' => __eva('Tenant is not valid.')]);
		}

		$restaurant = Restaurant::find($tenantId);
		if (!$restaurant) {
			return $this->response(['success' => false, 'error' => __eva('Tenant is not valid.')]);
		}

		return $this->response([
			'success' => true,
			'status' => [
				'hasSlotsBlocked' => $restaurant->hasDateBlockedSlots($date_string),
				'hasTablesBlocked' => $restaurant->hasDateBlockedTables($date_string)
			]
		]);
	}
}
