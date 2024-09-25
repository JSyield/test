<?php

namespace Alexr\Resources;

use Evavel\Http\Request\Request;
use Evavel\Resources\Fields\BelongsTo;
use Evavel\Resources\Fields\BelongsToMany;
use Evavel\Resources\Fields\HasMany;
use Evavel\Resources\Fields\ID;
use Evavel\Resources\Fields\Panel;
use Evavel\Resources\Fields\Text;
use Evavel\Resources\Resource;

class User extends Resource
{
	public static $modelClass = \Alexr\Models\User::class;

	protected $field_id = 'id';

	public static $title = 'name';

	public static $search = ['name', 'email', 'role'];

	public static function label()
	{
		return __eva('Users');
	}

	public static function labelSingular()
	{
		return __eva('User');
	}

	public static function indexQuery(Request $request, $query)
	{
		// This resource is accessed only by super admin
		//if ($request->tenantId()){
		//    $query->fromTenant($request->tenantId());
		//}
		//$query->where('role', SRR_UserRole::OWNER);
		return $query;
	}

	public function fields(Request $request) {
		return [

			ID::make(__eva('ID'), 'id')
			  ->sortable()->showOnIndex(true),

			Text::make(__eva('Name'), 'name')
			    ->sortable()
				//->required()
				->rules('required', 'max:50')
			    ->placeholder('THE CUSTOMER NAME')
			    ->suggestions(['Alejandro', 'Eva', 'Bruno'])
			    ->help('This is the name of the Customer'),

			Text::make(__eva('Role'), 'role')
			    ->rules('required')
			    ->sortable(),

			Panel::make(__eva('Details'), [

				Text::make(__eva('Email'), 'email')
					//->required()
					->sortable()
				    ->rules('required')
					//->readonly()
					->placeholder('THE CUSTOMER EMAIL')
				    ->help('This is the Email of the Customer'),

				Text::make(__eva('Phone'), 'phone')
				    ->help('Telephone of the Client'),

			])->help('Some help text here'),


			/*Text::make(__eva('Bookings'), function($item){
				//if (!$this->request->tenantId()) return '-';
				return SRR_Booking::where('user_id', $item->id)
					//->where('restaurant_id', $this->request->tenantId())
					->count();
			})->showOnDetail(false),
			*/

			/*Text::make(__eva('Restaurants'), function($item) {
				return count($item->restaurants);
			})->showOnDetail(false),*/

			BelongsTo::make(__eva('Restaurant'), 'restaurant', Restaurant::class),

			HasMany::make(__eva('Bookings'), 'bookings', Booking::class),

			//HasMany::make(__eva('Bookings'), 'bookings', SRR_Resource_Booking::class),
			BelongsToMany::make(__eva('Restaurants'), 'restaurants', Restaurant::class)
		];
	}
}
