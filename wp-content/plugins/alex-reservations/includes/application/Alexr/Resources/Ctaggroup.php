<?php

namespace Alexr\Resources;

use Evavel\Http\Request\Request;
use Evavel\Resources\Fields\BelongsTo;
use Evavel\Resources\Fields\Boolean;
use Evavel\Resources\Fields\ID;
use Evavel\Resources\Fields\Number;
use Evavel\Resources\Fields\Text;
use Evavel\Resources\Fields\Textarea;
use Evavel\Resources\Resource;

class Ctaggroup extends Resource
{
	public static $modelClass = \Alexr\Models\CTagGroup::class;

	protected $field_id = 'id';

	public static $title = 'name';

	public static $search = ['name'];

	public static function label() {
		return __eva('Customer Tag Groups');
	}

	public static function labelSingular() {
		return __eva('Customer Tag Group');
	}

	public function fields(Request $request)
	{
		return [
			ID::make(__eva('ID'), 'id'),
			BelongsTo::make(__eva('Restaurant'), 'restaurant', Restaurant::class),
			Text::make(__eva('Name'), 'name'),
			Number::make(__eva('Order'), 'ordering'),
			Text::make(__eva('Background color'), 'backcolor'),
			Text::make(__eva('Color'), 'color'),
			Boolean::make(__eva('is_deletable'), 'is_deletable'),
			Textarea::make(__eva('Notes'), 'notes')
		];
	}
}
