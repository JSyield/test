<?php

namespace Alexr\Resources;

use Evavel\Eva;
use Evavel\Resources\Resource;
use Evavel\Http\Request\Request;

use Evavel\Resources\Fields\ID;
use Evavel\Resources\Fields\Text;
use Evavel\Resources\Fields\Textarea;
use Evavel\Resources\Fields\HasMany;
use Evavel\Resources\Fields\BelongsToMany;
use Evavel\Resources\Fields\Boolean;
use Evavel\Resources\Fields\Date;
use Evavel\Resources\Fields\DateTime;
use Evavel\Resources\Fields\Timezone;


class Restaurant extends Resource
{
	public static $modelClass = \Alexr\Models\Restaurant::class;

	protected $field_id = 'id';

	public static $title = 'name';

	public static $search = ['name'];

	public static function label()
	{
		return __eva('Restaurants');
	}

	public static function labelSingular()
	{
		return __eva('Restaurant');
	}

	public static function indexQuery(Request $request, $query)
	{
		$user = Eva::make('user');

		// Limit to owner restaurants
		if ($user->role == 'owner') {
			//$query->debug();
			$query->whereIn('id', $user->restaurantsIds());
		}
	}

	public function fields(Request $request) {

		return [

			DateTime::make(__eva('Date'), 'date_modified')
			        ->format('Y-m-d H:i:s'),

			Timezone::make(__eva('Timezone'), 'timezone')
			        ->required()
			        ->searchable(),

			ID::make(__eva('ID'), 'id')->showOnIndex(true),

			Boolean::make(__eva('Active'), 'active')
			       ->help(__eva('If not active the booking form will be disabled.')),

			Text::make(__eva('Name'), 'name'),

			/*Text::make(__eva('Users'), function($item){
				return count($item->users);
			}),
			Text::make(__eva('Bookings'), function($item){
				return count($item->bookings);
			}),
			Text::make(__eva('Customers'), function($item){
				return count($item->customers);
			}),
			Text::make(__eva('Employees'), function($item){
				return count($item->employes);
			}),*/

			Textarea::make(__eva('Notes'), 'notes'),

			//BelongsToMany::make(__eva('Owners'), 'users', SRR_Resource_User::class),
			HasMany::make(__eva('Bookings'), 'bookings', Booking::class),
			//HasMany::make(__eva('Customers'), 'customers', SRR_Resource_Customer::class),
			//HasMany::make(__eva('Employees'), 'employes', SRR_Resource_Employe::class),
		];
	}

	public function policy() {
		return [
			'authorizedToCreate' => true,
			'authorizedToDelete' => true,
			'authorizedToUpdate' => true,
			'authorizedToView' => true
		];
	}

	public function actions(Request $request) {
		return [];
	}

	/**
	 * Settings fields
	 *
	 * @return array
	 */
	// Settings will be saved in the meta restaurant_meta table
	// The key is the attribute
	/*
		url => url
		general => { ejemplo: ..., ejemplo2: ..., ejemplo3: ...},
		schedules => { ejemplo: ..., ejemplo2: ..., ejemplo3: ...},
	*/
	public function settings() {

		return [

			new \settings\Panel(__eva('General'), 'general', [
				\settings\Text::make('General 1', 'st_general1'),
				\settings\Select::make('General 2', 'st_general2'),
				\settings\Boolean::make('General 3', 'st_general3'),
			]),

			new \settings\Panel(__eva('Schedules'), 'schedules', [
				\settings\Text::make('Schedules 1', 'st_schedules1'),
				\settings\Select::make('Schedules 2', 'st_schedules2'),
				\settings\Boolean::make('Schedules 3', 'st_schedules3'),
			]),

		];


	}
}
