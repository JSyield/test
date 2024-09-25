<?php

namespace Alexr\Resources;

use Evavel\Http\Request\Request;
use Evavel\Resources\Fields\ID;
use Evavel\Resources\Fields\Text;
use Evavel\Resources\Resource;

class Floor extends Resource
{
	public static $modelClass = \Alexr\Models\Floor::class;

	protected $field_id = 'id';

	public static $title = 'name';

	public static $search = ['name'];

	public static function label() {
		return __eva('Floors');
	}

	public static function labelSingular() {
		return __eva('Floor');
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
			Text::make(__eva('Name'), 'name')->required(),

			// I need these fields for saving from the FloorPlan
			Text::make('canvas', 'canvas')
				->showOnUpdate(false)
				->showOnCreation(false)
				->saveOnUpdate()
				->saveOnCreate(),
		];
	}

}
