<?php

namespace Alexr\Resources;

use Evavel\Http\Request\Request;
use Evavel\Resources\Fields\BelongsTo;
use Evavel\Resources\Fields\Boolean;
use Evavel\Resources\Fields\ID;
use Evavel\Resources\Fields\Number;
use Evavel\Resources\Fields\Select;
use Evavel\Resources\Fields\Text;
use Evavel\Resources\Resource;

class Table extends Resource
{
	public static $modelClass = \Alexr\Models\Table::class;

	protected $field_id = 'id';

	public static $title = 'name';

	public static $search = ['name'];

	public static function label() {
		return __eva('Tables');
	}

	public static function labelSingular() {
		return __eva('Table');
	}

	public static function indexQuery(Request $request, $query)
	{
		if ($request->tenantId()){
			$query->fromTenant($request->tenantId());
		}
	}

	public function fields(Request $request)
	{
		return [
			ID::make(__eva('ID'), 'id'),

			Text::make(__eva('Name'), 'name')
				->stacked()
				->styleInline('50%')
			    ->required(),

			Boolean::make(__eva('Bookable online'), 'bookable_online')
			       ->styleInline('50%')
			       ->stacked()
			       ->typeSwitch(),

			BelongsTo::make(__eva('Area'), 'area', Area::class)
			         ->stacked()
			         ->styleInline('100%')
			         ->required(),

			/*Select::make(__eva('Type', 'type'))
			      ->styleInline('50%')
			      ->stacked()
			      ->options([
				      'regular' => __eva('Regular'),
				      'hightop' => __eva('High-top'),
				      'counter' => __eva('Counter'),
				      'bar' => __eva('Bar'),
				      'outdoor' => __eva('Outdoor'),
			      ])
			      ->showOnCreation(true)
			      ->showOnUpdate(true),*/

			Select::make(__eva('Min seats'), 'min_seats')
			      ->options($this->numberOptions(1,20))
			      ->stacked()
			      ->styleInline('50%')
			      ->required(),

			Select::make(__eva('Max seats'), 'max_seats')
		        ->options($this->numberOptions(1,20))
				->stacked()
				->styleInline('50%')
				->required(),

			Boolean::make(__eva('Shareable'), 'shareable')
			       ->styleInline('100%')
			       ->stacked()
			       ->typeSwitch()
			       ->help(__eva('Shareable tables are not available for online reservations. They are assigned manually, one seat at a time, to different bookings.')),

			/* @TODO no esta listo todavia
			Number::make(__eva('Price 1'), 'amount_1')
				->step(0.01)->min(0)
			    ->stacked()
			    ->styleInline('50%'),

			Number::make(__eva('Price 2'), 'amount_2')
				->step(0.01)->min(0)
			    ->stacked()
			    ->styleInline('50%'),

			Number::make(__eva('Price 3'), 'amount_3')
				->step(0.01)->min(0)
			    ->stacked()
			    ->styleInline('50%'),

			Number::make(__eva('Price 4'), 'amount_4')
				->step(0.01)->min(0)
			    ->stacked()
			    ->styleInline('50%'),

			Number::make(__eva('Price 5'), 'amount_5')
				->step(0.01)->min(0)
			    ->stacked()
			    ->styleInline('50%'),

			Number::make(__eva('Price 6'), 'amount_6')
				->step(0.01)->min(0)
			    ->stacked()
			    ->styleInline('50%'),
			*/

			/*Select::make(__eva('Priority'), 'priority')
			      ->options($this->numberOptions(1,10))
			      ->stacked()
			      ->help(__eva("Indicates preference in assigning tables online. <br> 1 means the table will be assigned before the rest.<br> 10 means the last table to be assigned."))
			      ->required(),*/



			//Boolean::make(__eva('Bookable staff'), 'bookable_staff'),




			// OLD system, Not using anymore
			/*Select::make(__eva('Shape', 'shape'))
				->options([
					'circular' => __eva('Circular'),
					'square' => __eva('Square'),
					'rect' => __eva('Rectangular')
				])
				->showOnCreation(false)
				->showOnUpdate(false)
				->saveOnUpdate()
				->saveOnCreate(),*/ // Do not appear on creation but can be saved when updating

			// I need these fields for saving from the FloorPlan
			Text::make('canvas', 'canvas')
			    ->showOnUpdate(false)
			    ->showOnCreation(false)
			    ->saveOnUpdate()
				->saveOnCreate(),
		];
	}

	protected function numberOptions($min = 1, $max = 10)
	{
		$list = [];
		for ($i = $min; $i <= $max; $i++){
			$list[$i] = $i;
		}
		return $list;
	}
}
