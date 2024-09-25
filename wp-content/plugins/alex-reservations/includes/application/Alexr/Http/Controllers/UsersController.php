<?php

namespace Alexr\Http\Controllers;

use Alexr\Enums\UserRole;
use Alexr\Models\Restaurant;
use Alexr\Models\Token;
use Alexr\Models\User;
use Evavel\Eva;
use Evavel\Http\Controllers\Controller;
use Evavel\Http\Request\Request;
use Evavel\Query\Query;
use Evavel\Support\Str;

class UsersController extends Controller
{
	protected function isAdministrator()
	{
		$user = Eva::make('user');
		return $user->role == UserRole::ADMINISTRATOR;
	}

	public function index(Request $request)
	{
		if (!$this->isAdministrator()){
			return $this->response(['success' => false, 'error' => __eva('You cannot view users.')]);
		}

		$tableName = 'users';

		$perPage = isset($request->params['per_page']) ? $request->params['per_page'] : 25;
		$currentPage = isset($request->params['page']) ? $request->params['page'] : 1;
		$search = isset($request->params['search']) ? $request->params['search'] : null;

		$orderBy = isset($request->params['order']) ? $request->params['order'] : 'name';
		$orderBy = empty($orderBy) ? 'name' : $orderBy;

		$orderByDirection = isset($request->params['direction']) ? $request->params['direction'] : 'asc';
		$orderByDirection = empty($orderByDirection) ? 'asc' : $orderByDirection;

		// When using Query does not have access to $appends parameter of the model
		$query_count = Query::table($tableName);

		$query = User::orderBy("{$tableName}.{$orderBy}", $orderByDirection)
		                   ->page($currentPage, $perPage);

		// Filter search
		$this->applySearch($query_count, $search);
		$this->applySearch($query, $search);

		// Do the query
		$total_arr = $query_count->count($perPage);
		$rows = $query->get()->toArray();


		// Now grab the WP users and attach the data to each row
		$rows = $this->attachWpUsers($rows);

		// Attach restaurants and roles data needed
		$rows = $this->attachRestaurantsAndRolesData($rows);

		// Attack auth tokens
		$rows = $this->attachAuthTokens($rows);

		return $this->response([
			'success' => true,
			'total' => $total_arr['count'],
			'resources' => $rows,
			'per_page' => $perPage,
			'page' => $currentPage,
			'total_pages' => $total_arr['pages'],

			// These are needed for creating/updating the user
			'all_restaurants' => Restaurant::get()->toArray(),
			'all_roles' => UserRole::listing()
		]);
	}

	// Attach the WP-user data to each User
	protected function attachWpUsers($users)
	{
		$tableName = evavel_wp_table_users();

		// Find wp users id
		$wpusers_id = array_map(function($user){
			return intval($user->wp_user_id);
		}, $users);

		// Filter them
		$wpusers_id = array_filter($wpusers_id, function($user_id){
			return $user_id > 0;
		});

		// Request WP users with those IDS
		$wpusers = Query::table($tableName, null, true)
			->whereIn('ID', $wpusers_id)
			->get();

		$users = array_map(function($user) use($wpusers) {
			$user->wpuser = null;
			$user->wpuser_link = null;

			foreach($wpusers as $wpuser) {
				if ($wpuser->ID == $user->wp_user_id)
				{
					$wp_user_data = get_userdata($user->wp_user_id);
					$wp_user_roles = $wp_user_data->roles;

					$wpuser->user_pass = null;
					$wpuser->user_activation_key = null;
					$user->wpuser = $wpuser;
					$user->wpuser_link = get_edit_user_link($wpuser->ID);
					$user->wpuser_roles = $wp_user_roles;
				}
			}
			return $user;
		}, $users);

		return $users;
	}

	protected function attachRestaurantsAndRolesData($users)
	{
		// Just want to know how many restaurants are managed and which roles they have
		// Do not need the full data of the restaurants
		// So querying the relationship table is enough
		return array_map(function($user){
			$user->restaurants_roles = Query::table('restaurant_user')
				->where('user_id', $user->id)
				->get();
			return $user;
		}, $users);
	}

	protected function attachAuthTokens($users)
	{
		return array_map(function($user){
			$user->tokens = Query::table('tokens')
                    ->where('user_id', $user->id)
                    ->get();
			return $user;
		}, $users);
	}

	public function user(Request $request)
	{
		$user = User::find($request->userId);

		return $this->response([
			'user' => $user,
			'restaurants' => $user->restaurants->toArray(),
			'tokens' => Query::table('tokens')
			                 ->where('user_id', $user->id)
			                 ->get()
		]);
	}

	public function update(Request $request)
	{
		if (!$this->isAdministrator()){
			return $this->response(['success' => false, 'error' => __eva('You cannot update users.')]);
		}

		$user = User::find($request->userId);

		return $this->updateUserData($request, $user, false);
	}

	public function create(Request $request)
	{
		if (!$this->isAdministrator()){
			return $this->response(['success' => false, 'error' => __eva('You cannot create users.')]);
		}

		$params = $request->body_params;

		// Check same email does not exists
		$user_found = User::where('email', $params['email'])->first();
		if ($user_found) {
			return $this->response(['success' => false, 'error' => __eva('That email already exists.')]);
		}

		// Check same WP user is not used
		$user_found = User::where('wp_user_id', $params['wp_user_id'])->first();
		if ($user_found) {
			return $this->response(['success' => false, 'error' => __eva('That WP user has already been attached.')]);
		}

		$user = new User();
		$user->uuid = Str::uuid('us');

		// Bu default is creating a normal USER
		$params = $request->body_params;
		$role = isset($params['role']) ? $params['role'] : UserRole::USER;

		if ($role == UserRole::ADMINISTRATOR){
			$user->role = UserRole::ADMINISTRATOR;
		} else {
			$user->role = UserRole::USER;
		}

		return $this->updateUserData($request, $user, true);
	}

	protected function updateUserData(Request $request, $user, $is_new = true)
	{
		$params = $request->body_params;

		// Administrator cannot be deactivated and cannot change the role
		if ($user->role != UserRole::ADMINISTRATOR){
			$user->active = isset($params['active']) ? $params['active'] : true;
			$user->role = UserRole::USER; // General role for letting the user access the dashboard
		} else {
			$user->active = true;
		}

		$user->name = isset($params['name']) ? $params['name'] : 'Name';
		$user->first_name = isset($params['first_name']) ? $params['first_name'] : 'First';
		$user->last_name = isset($params['last_name']) ? $params['last_name'] : 'Last';
		$user->email = isset($params['email']) ? $params['email'] : '';
		$user->phone = isset($params['phone']) ? $params['phone'] : '';
		$user->country_code = isset($params['country_code']) ? $params['country_code'] : '';
		$user->dial_code = isset($params['dial_code']) ? $params['dial_code'] : '';
		$user->pin_code = isset($params['pin_code']) ? $params['pin_code'] : '';

		// To generate the ID for new user
		if (!$user->id){
			$user->save();
		}

		$this->attachTheWpUser($request, $user);

		$this->attachTheRestaurants($request, $user);

		$user->save();

		return $this->response(['success' => true, 'user' => $user]);
	}

	protected function attachTheWpUser(Request $request, $user)
	{
		$params = $request->body_params;

		$wp_user_id = isset($params['wp_user_id']) ? $params['wp_user_id'] : null;
		$user->wp_user_id = $wp_user_id;

		$should_create_wpuser = false;

		// Create WP User if null
		if ($wp_user_id == null || $wp_user_id == 'null' || $wp_user_id == 0) {
			$user->wp_user_id = null;
			$should_create_wpuser = true;
		}

		// Check the WP user has not been removed from WP
		else {
			$wpuser = Query::table(evavel_wp_table_users(), null, true)
			               ->where('ID', $wp_user_id)
			               ->first();
			if (!$wpuser) {
				$should_create_wpuser = true;
			}
		}

		if ($should_create_wpuser) {
			$user->wp_user_id = $this->createWpUserIfNotExist($params['name'], $params['email']);
		}
	}

	public function createWpUserIfNotExist($name, $email)
	{
		// Find the user by email in case it exists
		$tableName = evavel_wp_table_users();

		$wpuser = Query::table($tableName, null, true)
			->where('user_email', $email)
			->first();

		if ($wpuser) {
			return $wpuser->ID;
		}

		$first = $name;
		$last = '';
		$first_last = explode(' ', $name);
		if (is_array($first_last) && count($first_last) > 1) {
			$first = $first_last[0];
			$last = str_replace($first.' ', '', $name);
		}
		$user_login = str_replace(' ', '', strtolower($name));
		if (empty($user_login)){
			$user_login = 'User_'.rand(100000,999999);
		}

		$attrs = [
			'user_login' => $user_login,
			'user_pass' => uniqid(),
			'user_email' => $email,
			'first_name' => $first,
			'last_name' => $last,
			'display_name' => $name,
			'role' => 'subscriber'
		];

		$wpuser_id = evavel_create_wp_user($attrs);

		if (is_wp_error($wpuser_id)) {
			return null;
		}

		return $wpuser_id;
	}

	protected function attachTheRestaurants(Request $request, $user)
	{
		$params = $request->body_params;

		$list_restaurants = isset($params['restaurants']) ? $params['restaurants'] : [];
		$list_restaurants = json_decode($list_restaurants);
		if (!is_array($list_restaurants)) return;

		$list_ids = array_map(function($item){ return $item->id;}, $list_restaurants);

		$table = 'restaurant_user';

		// Sync current restaurants
		$user->restaurants()->sync($list_ids);

		// Update current ones fields and add the new ones
		foreach($list_restaurants as $item) {

			$row = Query::table($table)
				->where('restaurant_id', $item->id)
				->where('user_id', $user->id)
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
						'restaurant_id' => $item->id,
						'user_id' => $user->id,
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
		$with_wp_user = $params['with_wp_user'];
		//ray($with_wp_user);

		if (!is_array($list)){
			$list = explode(',', $list);
		}

		$users = User::whereIn('id', $list)->get();
		$users_wp = $users->map( function($user){ return ['id' => $user->id, 'wp_id' =>  $user->wp_user_id]; } )->toArray();

		// Any user with the WP role different from administrator can be deleted
		$messages = [];

		foreach($users_wp as $user_data)
		{
			$wp_user = get_user_by('id', $user_data['wp_id']);

			// Delete dashboard user
			User::whereIn('id', [$user_data['id']])->delete();

			if ($with_wp_user == 'yes' && !in_array('administrator', $wp_user->roles) )
			{
				evavel_delete_wp_user($user_data['wp_id']);
			}
			else
			{
				$messages[] = __eva('WordPress Administrator cannot be deleted.');
			}
		}

		if (!empty($messages)){
			return $this->response([ 'success' => true, 'message' => implode(', ', $messages) ]);
		}

		return $this->response([ 'success' => true, 'message' => __eva('Deleted') ]);
	}


	// TOKENS
	//----------------------------------------------------------

	public function listTokens(Request $request)
	{
		$tokens = Token::get()->toArray();
		return $this->response([ 'success' => true, 'tokens' => $tokens ]);
	}

	public function clearUserTokens(Request $request)
	{
		$user_id = $request->userId;

		Token::where('user_id', $user_id)->delete();

		return $this->response([ 'success' => true, 'message' => __eva('Cleared') ]);
	}

	public function clearAllTokens(Request $request)
	{
		Token::where('id', '>=', 1)->delete();

		return $this->response([ 'success' => true, 'message' => __eva('Cleared') ]);
	}

	// Get user restaurants associated
	//----------------------------------------------------------
	/*public function restaurants(Request $request)
	{
		$user = User::find($request->userId);

		return $this->response(['restaurants' => $user->restaurants->toArray()]);
	}*/


	// QUERY FILTERS
	//---------------------------------------------------------

	public function applySearch(Query $query, $search_value)
	{
		if (!$search_value) return $query;

		$this->querySearch($query, ['name', 'email', 'phone'], $search_value);
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
