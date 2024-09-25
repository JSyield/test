<?php

namespace Alexr\Models;

use Evavel\Models\Model;
use Evavel\Support\Str;

class Combination  extends Model
{
	public static $table_name = 'combinations';
	public static $table_meta = false;
	public static $pivot_tenant_field = 'restaurant_id';

	protected $visible = [
		'id', 'name', 'floor_id', 'area_id', 'min_seats', 'max_seats',
		'priority', 'ordering', 'bookable_staff', 'bookable_online'
	];

	protected $appends = ['list_tables'];

	protected $casts = [
		'id' => 'int',
		'floor_id' => 'int',
		'min_seats' => 'int',
		'max_seats' => 'int',
		'ordering' => 'int',
		'priority' => 'int',
		'bookable_staff' => 'boolean',
		'bookable_online' => 'boolean',
		'settings' => 'json'
	];

	public static function booted()
	{
		// Associate the tables after the model is created
		static::created(function($model) {

			$request = evavel_make('request');

			$list = json_decode($request->list_tables, true);

			$list_ids = evavel_collect($list)
				->filter(function ($isChecked) {
					return $isChecked;
				})
				->keys()
				->all();

			if (!empty($list_ids)){
				$model->tables()->sync($list_ids);
			}
		});
	}

	public function restaurant()
	{
		return $this->belongsTo(Restaurant::class);
	}

	public function floor()
	{
		return $this->belongsTo(Floor::class);
	}

	public function area()
	{
		return $this->belongsTo(Area::class);
	}

	public function tables()
	{
		return $this->belongsToMany(Table::class, 'combination_table', 'combination_id', 'table_id');
	}

	public function getListTablesAttribute()
	{
		return $this->tables->toArray();
	}

	public function  getListTablesIdAttribute()
	{
		return $this->tables->map(function($table){
			return intval($table->id);
		})->toArray();
	}

	public function setListTablesAttribute($value)
	{
		// Cuando edit desde el popup necesito esta funcion
		// Cuando creo por primera vez esta funcion es llamada desde ResourceCreateController
		// pero genera en la table combination_table un problema porque asigna combination_id como null
		// Por eso compruebo si id no es null

		//ray('setListTablesAttribute ' . $this->id);
		//ray($value);

		if (!$this->id) return;

		$list_ids = explode(',', $value);
		if (!is_array($list_ids)) return;

		$list_ids = array_map(function($id) { return intval($id); }, $list_ids);

		// No hay mesas -> array[0 => 0]
		if (is_array($list_ids) && count($list_ids) == 1 && $list_ids[0] == 0){
			$list_ids = [];
		}

		$this->tables()->sync($list_ids);
	}

}
