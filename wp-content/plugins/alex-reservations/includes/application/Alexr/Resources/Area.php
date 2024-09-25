<?php

namespace Alexr\Resources;

use Evavel\Http\Request\Request;
use Evavel\Resources\Fields\BelongsTo;
use Evavel\Resources\Fields\Boolean;
use Evavel\Resources\Fields\ID;
use Evavel\Resources\Fields\Image;
use Evavel\Resources\Fields\Select;
use Evavel\Resources\Fields\Text;
use Evavel\Resources\Resource;

class Area extends Resource
{
	public static $modelClass = \Alexr\Models\Area::class;

	protected $field_id = 'id';

	public static $title = 'name';

	public static $search = ['name'];

	public static function label() {
		return __eva('Areas');
	}

	public static function labelSingular() {
		return __eva('Area');
	}

	public static function indexQuery(Request $request, $query)
	{
		if ($request->tenantId()){
			$query->fromTenant($request->tenantId());
		}
	}

	public function fields(Request $request)
	{
		// Using 2 modes:
		// Floors:Areas:Tables
		// Areas:Tables

		$add_floor = false;

		if (
			($request->editMode == 'update' || $request->editMode == 'create')
		    && ( $request->viaResource != null && $request->viaResource != 'null' )
		){
			$add_floor = true;
		}

		$result = [
			ID::make(__eva('ID'), 'id'),
			Text::make(__eva('Name'), 'name')->required(),

			/*Select::make(__eva('Priority'), 'priority')
			      ->options($this->numberOptions(1,10))
			      ->required(),*/

			Boolean::make(__eva('Bookable online'), 'bookable_online')
				->help(__eva('If this is not online the table included will not be bookable online.'))
		        ->typeSwitch(),

			Image::make(__eva('Image'), 'image_url')
				->options([
					'accept' => 'image/png, image/jpeg',
					'maxWidth' => 1200,
					'maxHeight' => 1200,
					'resize' => false,
					'checkDimensions' => true,
				]),

			// I need these fields for saving from the FloorPlan
			Text::make('canvas', 'canvas')
			    ->showOnUpdate(false)
			    ->showOnCreation(false)
			    ->saveOnUpdate(),
		];

		if ($add_floor){
			$result[] = BelongsTo::make(__eva('Floor'), 'floor', Floor::class);
		}

		return $result;
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
