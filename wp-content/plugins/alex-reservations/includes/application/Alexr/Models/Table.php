<?php

namespace Alexr\Models;

use Evavel\Models\Model;

class Table extends Model
{
	public static $table_name = 'tables';
	public static $table_meta = false;
	public static $pivot_tenant_field = 'restaurant_id';

	protected $visible = [
		'id', 'name', 'area_id', 'min_seats', 'max_seats',
		'priority', 'ordering', 'bookable_staff', 'bookable_online',
		//'shape',
		'type', 'shareable',
	];

	protected $appends = ['canvas'];

	protected $casts = [
		'id' => 'int',
		'area_id' => 'int',
		'min_seats' => 'int',
		'max_seats' => 'int',
		'ordering' => 'int',
		'priority' => 'int',
		'bookable_staff' => 'boolean',
		'bookable_online' => 'boolean',
		'shareable' => 'boolean',
		'settings' => 'array',

		// Relacion booking_table
		//'pivot_table_id' => 'int',
		//'pivot_booking_id' => 'int',
		//'pivot_seats' => 'json',
	];

	public function restaurant()
	{
		return $this->belongsTo(Restaurant::class);
	}

	public function area()
	{
		return $this->belongsTo(Area::class);
	}

	public function getCanvasAttribute()
	{
		$settings = $this->settings;
		return isset($this->settings['canvas']) ? $this->settings['canvas'] : [];
	}

	public function setCanvasAttribute($value)
	{
		// When updating from the form I do not receive canvas
		// so don't want to save null
		if ($value == null) return;

		$settings = $this->settings;
		if (!is_array($settings)){
			$settings = [];
		}

		if (is_array($value)) {
			$settings['canvas'] = $value;
		} else if (is_string($value)){
			$settings['canvas'] = json_decode($value, true) ;
		}


		$this->attributes['settings'] = $settings;
	}

	/**
	 * Delete tables and combinations if areaa_id does not exists
	 * @param $restaurant_id
	 *
	 * @return void
	 */
	public static function removeTablesAndCombinationsWithNoAreas($restaurant_id)
	{
		$areas = Area::where('restaurant_id', $restaurant_id)->get();
		$tables = Table::where('restaurant_id', $restaurant_id)->get();
		$combinations = Combination::where('restaurant_id', $restaurant_id)->get();

		$areas_id = $areas->map(function($area) { return $area->id; })->toArray();
		$tables_id = $tables->map(function($table) { return [ 'id' => $table->id, 'area_id' => $table->area_id]; })->toArray();
		$combinations_id = $combinations->map(function($combination) { return [ 'id' => $combination->id, 'area_id' => $combination->area_id]; })->toArray();

		// Encontrar y borrar mesas huerfanas de area
		$list_to_remove = [];
		foreach($tables_id as $table) {
			if (!in_array($table['area_id'], $areas_id)) {
				$list_to_remove[] = $table['id'];
			}
		}
		if (count($list_to_remove) > 0) {
			Table::whereIn('id', $list_to_remove)->delete();
		}

		// Encontrar combinaciones huerfanas de area
		$list_to_remove = [];
		foreach($combinations_id as $combination) {
			if (!in_array($combination['area_id'], $areas_id)) {
				$list_to_remove[] = $combination['id'];
			}
		}
		if (count($list_to_remove) > 0) {
			Combination::whereIn('id', $list_to_remove)->delete();
		}
	}
}
