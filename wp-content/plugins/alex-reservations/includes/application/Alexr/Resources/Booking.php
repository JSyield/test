<?php

namespace Alexr\Resources;

use Alexr\Actions\ChangeDate;
use Alexr\Actions\ChangeStatus;
use Alexr\Enums\BookingStatus;
use Alexr\Enums\UserRole;
use Alexr\Filters\BookingDateFilter;
use Alexr\Filters\BookingDateRangeFilter;
use Alexr\Filters\BookingFutureFilter;
use Alexr\Filters\BookingStatusFilter;
use Alexr\Resources\Customer;
use Alexr\Resources\Lenses\BookingsToday;
use Evavel\Eva;
use Evavel\Http\Request\Request;
use Evavel\Resources\Fields\BelongsTo;
use Evavel\Resources\Fields\DateTime;
use Evavel\Resources\Fields\ID;
use Evavel\Resources\Fields\Select;
use Evavel\Resources\Fields\Text;
use Evavel\Resources\Resource;

class Booking extends Resource
{
	public static $modelClass = \Alexr\Models\Booking::class;

	protected $field_id = 'id';

	public static $title = 'name';

	public static $search = ['name', 'email', 'status', 'date'];

	public static function label() {
		return __eva('Bookings');
	}

	public static function labelSingular() {
		return __eva('Booking');
	}

	public static function indexQuery(Request $request, $query)
	{

		$user = Eva::make('user');

		if ($request->tenantId()){
			$query->fromTenant($request->tenantId());
		}

		// No lo estoy usando, era de antes
		if ($user->role == UserRole::OWNER){
			$query->whereIn('restaurant_id', $user->restaurantsIds());
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

			Text::make('Restaurant', function($model){
				return $model->restaurant->name;
			}),

			BelongsTo::make(__eva('Restaurant'), 'restaurant', Restaurant::class)
			         ->required(),

			BelongsTo::make(__eva('Customer'), 'customer', Customer::class),

			DateTime::make(__eva('Date'), 'date')
			        ->sortable()
			        ->required()
			        ->showOnIndex(true),

			Select::make(__eva('Status'), 'status')
			      ->options(BookingStatus::listing())
			      ->styles(BookingStatus::styles()),
		];
	}

	public function filters(Request $request) {
		return [
			new BookingStatusFilter(),
			new BookingDateFilter(),
			new BookingDateRangeFilter(),
			// new BookingFutureFilter()
		];
	}

	public function actions(Request $request) {
		return [
			new ChangeStatus(),
			new ChangeDate()
		];
	}

	public function lenses(Request $request) {
		return [
			new BookingsToday()
		];
	}
}
