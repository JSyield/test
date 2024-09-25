<?php

namespace Alexr\Resources;

use Evavel\Http\Request\Request;
use Evavel\Resources\Fields\BelongsTo;
use Evavel\Resources\Fields\ID;
use Evavel\Resources\Fields\Text;
use Evavel\Resources\Resource;

class Customer extends Resource
{
	public static $modelClass = \Alexr\Models\Customer::class;

	protected $field_id = 'id';

	public static $title = 'name';

	public static $search = ['name', 'email', 'phone'];

	public static function label() {
		return __eva('Customers');
	}

	public static function labelSingular() {
		return __eva('Customer');
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
			ID::make(__eva('ID'), 'id')
			  ->showOnIndex(true)
			  ->showOnDetail(true)
			  ->sortable()
			  ->textAlign('left'),

			Text::make(__eva('Name'), 'name'),


			BelongsTo::make(__eva('Restaurant'), 'restaurant', Restaurant::class)
			         ->required(),
		];
	}
}
