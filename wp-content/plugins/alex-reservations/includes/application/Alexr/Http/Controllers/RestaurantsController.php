<?php

namespace Alexr\Http\Controllers;

use Alexr\Enums\BookingStatus;
use Alexr\Enums\CurrencyType;
use Alexr\Enums\UserRole;
use Alexr\Models\Booking;
use Alexr\Models\Restaurant;
use Alexr\Models\User;
//use Carbon\Carbon;
use Evavel\Eva;
use Evavel\Http\Controllers\Controller;
use Evavel\Http\Request\Request;
use Evavel\Query\Query;

class RestaurantsController extends Controller
{
	protected function isAdministrator()
	{
		$user = Eva::make('user');
		return $user->role == UserRole::ADMINISTRATOR;
	}

	public function index(Request $request)
	{
		if (!$this->isAdministrator()){
			return $this->response(['success' => false, 'error' => __eva('You cannot view restaurants.')]);
		}

		$tableName = 'restaurants';

		$perPage = isset($request->params['per_page']) ? $request->params['per_page'] : 25;
		$currentPage = isset($request->params['page']) ? $request->params['page'] : 1;
		$search = isset($request->params['search']) ? $request->params['search'] : null;

		$orderBy = isset($request->params['order']) ? $request->params['order'] : 'name';
		$orderBy = empty($orderBy) ? 'name' : $orderBy;

		$orderByDirection = isset($request->params['direction']) ? $request->params['direction'] : 'asc';
		$orderByDirection = empty($orderByDirection) ? 'asc' : $orderByDirection;

		// When using Query does not have access to $appends parameter of the model
		$query_count = Query::table($tableName);

		$query = Restaurant::orderBy("{$tableName}.{$orderBy}", $orderByDirection)
		                   ->page($currentPage, $perPage);

		// Filter search
		$this->applySearch($query_count, $search);
		$this->applySearch($query, $search);

		// Do the query
		$total_arr = $query_count->count($perPage);
		$rows = $query->get()->toArray();

		// Attach Users to the restaurant
		$rows = $this->attachUsersAndRolesData($rows);

		return $this->response([
			'success' => true,
			'total' => $total_arr['count'],
			'resources' => $rows,
			'per_page' => $perPage,
			'page' => $currentPage,
			'total_pages' => $total_arr['pages'],

			'all_users' => User::get()->toArray(),
			'all_roles' => UserRole::listing()
		]);
	}

	protected function attachUsersAndRolesData($restaurants)
	{
		return array_map(function($restaurant){
			$restaurant->users_roles = Query::table('restaurant_user')
				->where('restaurant_id', $restaurant->id)
				->get();
			return $restaurant;
		}, $restaurants);
	}

	public function restaurant(Request $request)
	{
		$restaurant = Restaurant::find($request->restaurantId);

		return $this->response([
			'restaurant' => $restaurant,
			'users' => $restaurant->users->toArray()
		]);
	}

	/**
	 * Get some metrics for the restaurants associated to the user
	 * @param Request $request
	 *
	 * @return \WP_REST_Response
	 */
	public function metrics(Request $request)
	{
		$user = Eva::make('user');

		if ($user->role == 'administrator'){
			$tenants = Restaurant::get();
		} else {
			$tenants = $user->restaurants
				->filter(function($restaurant){
					return $restaurant->active == 1;
				});
		}


		$tenants = $tenants->map(function($restaurant){
			return $this->mapRestaurant($restaurant);
		})->toArray();

		$tenants = array_values($tenants);

		return $this->response(['success' => true, 'tenants' => $tenants]);
	}

	protected function mapRestaurant($restaurant)
	{
		$r_id = $restaurant->id;

		//$past_30_days = Carbon::now()->setTimezone($restaurant->timezone)->addDays(-30)->format('Y-m-d');
		//$past_30_days_with_hours = Carbon::now()->setTimezone($restaurant->timezone)->addDays(-30)->format('Y-m-d H:i:s');

		$past_30_days = evavel_date_now()->setTimezone($restaurant->timezone)->addDays(-30)->format('Y-m-d');
		$past_30_days_with_hours = evavel_date_now()->setTimezone($restaurant->timezone)->addDays(-30)->format('Y-m-d H:i:s');

		$total_bookings = Query::table('bookings')
		                       ->where('restaurant_id', $r_id)
		                       ->whereIn('status', BookingStatus::valid())
		                       ->onlyCount()
		                       ->get();

		$bookings_last_month = Query::table('bookings')
		                            ->where('restaurant_id', $r_id)
									->where('date', '>=', $past_30_days )
									->whereIn('status', BookingStatus::valid())
		                            ->onlyCount()
		                            ->get();

		$total_customers = Query::table('customers')
								->where('restaurant_id', $r_id)
								->onlyCount()
								->get();

		$new_customers_last_month = Query::table('customers')
		                        ->where('restaurant_id', $r_id)
								->where('date_created', '>=', $past_30_days )
		                        ->onlyCount()
		                        ->get();

		return [
			'id' => $r_id,
			'uuid' => $restaurant->uuid,
			'metrics' => [
				'total_bookings' => $total_bookings,
				'bookings_last_month' => $bookings_last_month,
				'total_customers' => $total_customers,
				'new_customers_last_month' => $new_customers_last_month,
			]
		];
	}

	public function update(Request $request)
	{
		if (!$this->isAdministrator()){
			return $this->response(['success' => false, 'error' => __eva('You cannot edit restaurants.')]);
		}

		$restaurant = Restaurant::find($request->restaurantId);

		$this->updateRestaurantData($request, $restaurant, false);
	}

	public function create(Request $request)
	{
		if (!$this->isAdministrator()){
			return $this->response(['success' => false, 'error' => __eva('You cannot create restaurants.')]);
		}

		return $this->updateRestaurantData($request, new Restaurant(), true);
	}

	protected function updateRestaurantData(Request $request, $restaurant, $is_new = true)
	{
		$params = $request->body_params;

		$should_refresh_dashboard = false;
		if ($restaurant->timezone != $params['timezone']) {
			$should_refresh_dashboard = true;
		}
		if ($restaurant->language != $params['language']) {
			$should_refresh_dashboard = true;
		}

		$restaurant->active = $params['active'];
		$restaurant->name = $params['name'];
		$restaurant->email = $params['email'];
		$restaurant->phone = $params['phone'];
		$restaurant->country_code = $params['country_code'];
		$restaurant->dial_code = $params['dial_code'];
		$restaurant->timezone = $params['timezone'];
		$restaurant->language = $params['language'];
		$restaurant->address = $params['address'];
		$restaurant->city = $params['city'];
		$restaurant->country = $params['country'];
		$restaurant->postal_code = $params['postal_code'];
		$restaurant->link_web = $params['link_web'];
		$restaurant->link_facebook = $params['link_facebook'];
		$restaurant->link_instagram = $params['link_instagram'];

		// To generate the ID for new restaurant
		if (!$restaurant->id){
			$restaurant->save();
		}

		$this->attachTheUsers($request, $restaurant);

		$restaurant->save();

		return $this->response([
			'should_refresh_dashboard' => $should_refresh_dashboard,
			'restaurant' => $restaurant
		]);
	}

	protected function attachTheUsers(Request $request, $restaurant)
	{
		$params = $request->body_params;

		$list_users = isset($params['users']) ? $params['users'] : [];
		$list_users = json_decode($list_users);
		if (!is_array($list_users)) return;

		$list_ids = array_map(function($item){ return $item->id;}, $list_users);

		$table = 'restaurant_user';

		// Sync current users
		$restaurant->users()->sync($list_ids);

		// Update current ones fields and add the new ones
		foreach($list_users as $item) {

			$row = Query::table($table)
			            ->where('restaurant_id', $restaurant->id)
			            ->where('user_id', $item->id)
			            ->first();

			// Update
			if ($row) {
				Query::table($table)
				     ->where('id', $row->id)
				     ->update( [
					     'role' => $item->user_role,
					     'settings' => is_string($item->user_settings) ? $item->user_settings : json_encode($item->user_settings)
				     ]);
			}

			// Add
			else {
				Query::table($table)
				     ->insert([
					     'restaurant_id' => $restaurant->id,
					     'user_id' => $item->id,
					     'role' => $item->user_role,
					     'settings' => is_string($item->user_settings) ? $item->user_settings : json_encode($item->user_settings)
				     ]);
			}
		}
	}

	public function delete(Request $request)
	{
		$params = $request->body_params;

		$list = $params['list'];

		if (!is_array($list)){
			$list = explode(',', $list);
		}

		Restaurant::whereIn('id', $list)->delete();

		//return $this->response([ 'success' => false, 'error' => __eva('Error deleting restaurants') ]);
		return $this->response([ 'success' => true, 'message' => __eva('Deleted') ]);
	}


	// QUERY FILTERS
	//---------------------------------------------------------

	public function applySearch(Query $query, $search_value)
	{
		if (!$search_value) return $query;

		$this->querySearch($query, ['name', 'email', 'phone', 'timezone'], $search_value);
	}

	public function querySearch($query, $where_fields, $search_value)
	{
		$closure = function($query) use($where_fields, $search_value) {
			for ($i = 0; $i < count($where_fields); $i++)
			{
				$w_field = $where_fields[$i];
				if ($i == 0){
					$query->where($w_field, 'like', $search_value);
				} else {
					$query->orWhere($w_field, 'like', $search_value);
				}
			}
			return $query;
		};

		$query->where($closure);
	}

}
