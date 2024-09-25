<?php

namespace Alexr\Resources;

use Evavel\Http\Request\Request;
use Evavel\Resources\Fields\BelongsTo;
use Evavel\Resources\Fields\Boolean;
use Evavel\Resources\Fields\Checkboxes;
use Evavel\Resources\Fields\ID;
use Evavel\Resources\Fields\Select;
use Evavel\Resources\Fields\Text;
use Evavel\Resources\Resource;

class Combination extends Resource
{
	public static $modelClass = \Alexr\Models\Combination::class;

	protected $field_id = 'id';

	public static $title = 'name';

	public static $search = ['name'];

	public static function label() {
		return __eva('Combinations');
	}

	public static function labelSingular() {
		return __eva('Combination');
	}

	public static function indexQuery(Request $request, $query)
	{
		if ($request->tenantId()){
			$query->fromTenant($request->tenantId());
		}
	}

	public function fields(Request $request) {

		$add_area_field = false;
		$add_floor_field = false;

		if ($request->viaResource == 'floors') {
			$add_floor_field = true;
		} else if ($request->viaResource == 'areas') {
			$add_area_field = true;
		}

		$fields = [ID::make( __eva( 'ID' ), 'id' )];

		if ($add_floor_field) {
			$fields[] = BelongsTo::make(__eva('Floor'), 'floor', Floor::class)->stacked();
		}

		if ($add_area_field) {
			$fields[] = BelongsTo::make(__eva('Area'), 'area', Area::class)->stacked();
		}

		$last_fields =  [
			Boolean::make(__eva('Bookable online'), 'bookable_online')
			       ->stacked()
			       ->typeSwitch(),

			Select::make(__eva('Min seats'), 'min_seats')
				->styleInline('50%')
				->stacked()
				->options($this->numberOptions(1,20))
				->required(),

			Select::make(__eva('Max seats'), 'max_seats')
				->styleInline('50%')
				->stacked()
				->options($this->numberOptions(1,20))
				->required(),

			/*Select::make(__eva('Priority'), 'priority')
		        ->options($this->numberOptions(1,10))
				->stacked()
				->help(__eva("Indicates preference in assigning tables online. <br> 1 means the table will be assigned before the rest.<br> 10 means the last table to be assigned."))
				->required(),*/


			Checkboxes::make(__eva('Tables'), 'list_tables')
	          ->stacked()
	          ->options( $add_floor_field
		          ? $this->tablesForFloor($request)
		          : $this->tablesForArea($request) )
	          ->saveAsString()
	          ->asHtml()
	          ->columns(1)
	          ->required(),
		];

		return array_merge($fields, $last_fields);
	}

	protected function numberOptions($min = 1, $max = 10)
	{
		$list = [];
		for ($i = $min; $i <= $max; $i++){
			$list[$i] = $i;
		}
		return $list;
	}

	protected function tablesForFloor(Request $request)
	{
		$floorId = $request->viaResourceId();

		$areas = \Alexr\Models\Area::where('floor_id', $floorId)
			->get()->values()->pluck('id');

		$tables = \Alexr\Models\Table::whereIn('area_id', $areas)
			->get()->mapWithKeys(function($table){
				return [$table->id => $table->name . " <em class='text-sm text-slate-500'>({$table->min_seats}-{$table->max_seats})</em>"];
			})->all();

		return $tables;

		/*return [
			'1' => 'Table 1',
			'2' => 'Table 2'
		];*/
	}

	protected function tablesForArea(Request $request)
	{
		$areaId = $request->viaResourceId();

		$tables = \Alexr\Models\Table::where('area_id', $areaId)
			->get()->mapWithKeys(function($table){
				return [$table->id => $table->name . " <em class='text-sm text-slate-500'>({$table->min_seats}-{$table->max_seats})</em>"];
			})->all();

		return $tables;
	}
}
