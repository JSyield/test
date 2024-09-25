<?php

namespace Alexr\Http\Controllers;

use Alexr\Models\Area;
use Alexr\Models\Booking;
use Alexr\Models\CTag;
use Alexr\Models\CTagGroup;
use Alexr\Models\Customer;
use Alexr\Models\Restaurant;
use Alexr\Models\Table;
use Alexr\Models\Traits\CsvHelpers;
//use Carbon\Carbon;
use Evavel\Eva;
use Evavel\Http\Controllers\Controller;
use Evavel\Http\Request\Request;
use Evavel\Models\Collections\Collection;
use Evavel\Query\Query;

class CustomersController extends Controller
{
	use CsvHelpers;

	public function index(Request $request)
	{
		$user = Eva::make('user');
		if (!$user->canManage($request->tenant)){
			return $this->response([ 'success' => false, 'error' => __eva("You cannot access.") ]);
		}

		$tableName = 'customers';

		$tenantId = $request->tenantId();

		$perPage = isset($request->params['per_page']) ? $request->params['per_page'] : 25;
		$currentPage = isset($request->params['page']) ? $request->params['page'] : 1;
		$search = isset($request->params['search']) ? $request->params['search'] : null;

		$orderBy = isset($request->params['order']) ? $request->params['order'] : 'name';
		$orderBy = empty($orderBy) ? 'name' : $orderBy;

		$orderByDirection = isset($request->params['direction']) ? $request->params['direction'] : 'asc';
		$orderByDirection = empty($orderByDirection) ? 'asc' : $orderByDirection;

		// When using Query does not have access to $appends parameter of the model
		$query_count = Query::table($tableName)->where('restaurant_id', $tenantId);

		$query = Customer::where('restaurant_id', $tenantId)
			->orderBy("{$tableName}.{$orderBy}", $orderByDirection)
			->page($currentPage, $perPage);


		// Filters visits
		$filters = isset($request->params['filters']) ? $request->params['filters'] : false;
		if ($filters){
			$this->applyFilters($query_count, $filters);
			$this->applyFilters($query, $filters);
		}

		// Filter tags
		$tags_id = isset($request->params['tags']) ? $request->params['tags'] : false;
		if ($tags_id) {
			$this->applyTags($query_count, $tags_id);
			$this->applyTags($query, $tags_id);
		}

		// Filter search
		$this->applySearch($query_count, $search);
		$this->applySearch($query, $search);

		// Do the query
		//Query::setDebug(true);
		//ray('QUERY CUSTOMERS');
		$total_arr = $query_count->count($perPage);
		//Query::setDebug(false);
		//ray('END QUERY CUSTOMERS');
		//ray($total_arr);

		$rows = $query->get()->toArray();

		return $this->response([
			'total' => $total_arr['count'],
			'resources' => $rows,
			'per_page' => $perPage,
			'page' => $currentPage,
			'total_pages' => $total_arr['pages'],
		]);
	}

	public function customersList(Request $request)
	{
		$params = $request->params;
		$customers_id = explode(',', $params['customers']);

		$items = Customer::whereIn('id', $customers_id)->get();

		return $this->response([
			'success' => true,
			'customers' => $items->toArray()
		]);
	}


	// QUERY FILTERS
	//---------------------------------------------------------

	public function applyFilters(Query $query, $filters)
	{
		foreach($filters as $filter){

			// visits_10, visits_20, etc
			if (preg_match('#visits_(\d+)#', $filter, $matches))
			{
				$num = intval($matches[1]);
				$query->where('visits', '>=', $num);
			}

			else if (preg_match('#last_visit_(\d+)_days#', $filter, $matches))
			{
				$num = intval($matches[1]);
				//$last_date = Carbon::now()->subDay($num)->format('Y-m-d');
				$last_date = evavel_date_now()->subDay($num)->format('Y-m-d');
				$query->where('last_visit', '>=', $last_date);
			}

			else if (preg_match('#last_visit_(\d+)_months#', $filter, $matches))
			{
				$num = intval($matches[1]);
				//$last_date = Carbon::now()->subMonth($num)->format('Y-m-d');
				$last_date = evavel_date_now()->subMonth($num)->format('Y-m-d');
				$query->where('last_visit', '>=', $last_date);
			}

			else if (preg_match('#last_visit_(\d+)_years#', $filter, $matches))
			{
				$num = intval($matches[1]);
				//$last_date = Carbon::now()->subYear($num)->format('Y-m-d');
				$last_date = evavel_date_now()->subYear($num)->format('Y-m-d');
				$query->where('last_visit', '>=', $last_date);
			}
		}

		return $query;
	}

	public function applyTags(Query $query, $tags_id)
	{
		// ERROR: is taking the id of the customer_ctag table
		/*for ($i = 0; $i < count($tags_id); $i++) {
			if ($i == 0) {
				$query->join('customer_ctag', 'customer_ctag.customer_id', '=', 'customers.id')
				      ->where('customer_ctag.ctag_id', $tags_id[0]);
			} else {
				$query->orWhere('customer_ctag.ctag_id', $tags_id[$i]);
			}
		}*/

		$list = Query::table('customer_ctag')->whereIn('ctag_id', $tags_id)->get();


		$list_ids = array_unique((new Collection($list))
			->map( function($ref) { return $ref->customer_id; } )
			->toArray());

		$query->whereIn('id', $list_ids);

		return $query;
	}

	public function applySearch(Query $query, $search_value)
	{
		if (!$search_value) return $query;

		$this->querySearch($query, ['first_name', 'last_name', 'name', 'email', 'phone'], $search_value);
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


	// CREATE CUSTOMER
	//---------------------------------------------------------

	public function create(Request $request)
	{
		$user = Eva::make('user');
		$restaurant_id = $request->tenant;

		if (!$user->canCreate('customers', $request->tenant)){
			return $this->response([ 'success' => false, 'error' => __eva("You cannot create customers.") ]);
		}

		$customer = new Customer();
		$customer->restaurant_id = $restaurant_id;

		return $this->updateCustomerData($request, $customer, true);
	}

	// UPDATE CUSTOMER requests
	//---------------------------------------------------------

	// Get single customer
	public function customer(Request $request)
	{
		$id = intval($request->customerId);

		$customer = Customer::find($id);

		if ($customer == null){
			return $this->response([ 'success' => false, 'error' => __eva("No customer found.") ]);
		}

		$user = Eva::make('user');
		if (!$user->canManage($customer->restaurant_id)){
			return $this->response([ 'success' => false, 'error' => __eva("You cannot access.") ]);
		}

		$bookings = Booking::where('customer_id', $id)
		                   ->orderBy('date', 'desc')
		                   ->get()->toArray();

		return $this->response([
			'customer' => $customer,
			'bookings' => $bookings,
			'areas'     => Area::where('restaurant_id', $customer->restaurant_id)->get()->toArray(),
			'tables'    => Table::where('restaurant_id', $customer->restaurant_id)->get()->toArray(),
		]);
	}

	public function update(Request $request)
	{
		$user = Eva::make('user');
		if (!$user->canEdit('customers', $request->tenant)){
			return $this->response([ 'success' => false, 'error' => __eva("You cannot edit this customer.") ]);
		}

		$customer = Customer::find($request->customerId);

		return $this->updateCustomerData($request, $customer, false);
	}

	protected function updateCustomerData(Request $request, $customer, $is_new = true)
	{
		$params = $request->body_params;

		$tags = json_decode($params['tags']);

		$customer->restaurant_id = $request->tenantId();
		$customer->first_name = $params['first_name'];
		$customer->last_name = $params['last_name'];
		$customer->name = $params['first_name'] . ' ' . $params['last_name'];
		$customer->company = $params['company'];
		$customer->email = $params['email'];
		$customer->phone = $params['phone'];
		$customer->language = $params['language'];
		$customer->dial_code_country = $params['dial_code_country'];
		$customer->country_code = $params['country_code'];
		$customer->dial_code = $params['dial_code'];
		$customer->notes = $params['notes'];
		$customer->birthday = $params['birthday'];
		$customer->gender = $params['gender'];

		$customer->agree_receive_email_marketing = intval($params['agree_receive_email_marketing']);
		$customer->agree_receive_email = intval($params['agree_receive_email']);
		$customer->agree_receive_sms = intval($params['agree_receive_sms']);

		$customer->save();

		$customer->tags()->sync($tags);

		return $this->response([ 'success' => true, 'customer' => $customer ]);
	}

	public function delete(Request $request)
	{
		$user = Eva::make('user');
		if (!$user->canDelete('customers', $request->tenant)){
			return $this->response([ 'success' => false, 'error' => __eva("You cannot delete customers.") ]);
		}

		$params = $request->body_params;

		$list = $params['list'];

		if (!is_array($list)){
			$list = explode(',', $list);
		}

		Customer::whereIn('id', $list)->delete();

		//return $this->response([ 'success' => false, 'error' => __eva('Error deleting customers') ]);
		return $this->response([ 'success' => true, 'message' => __eva('Deleted') ]);
	}

	public function merge(Request $request)
	{
		$user = Eva::make('user');
		if (!$user->canEdit('customers', $request->tenant)){
			return $this->response([ 'success' => false, 'error' => __eva("You cannot update customers.") ]);
		}

		$params = $request->body_params;

		$list = $params['list'];

		if (!is_array($list)){
			$list = explode(',', $list);
		}

		// First customer will receive all the bookings
		$first_id = array_shift($list);
		Booking::whereIn('customer_id', $list) ->update([
			'customer_id' => $first_id
		]);

		// Remove the other customers
		Customer::whereIn('id', $list)->delete();

		Customer::find($first_id)->calculateVisits();

		//return $this->response([ 'success' => false, 'error' => __eva('Error merging customers') ]);
		return $this->response([ 'success' => true, 'message' => __eva('Done') ]);
	}


	// CSV, PRINT PDF
	//-------------------------------------------------------------

	public function downloadCSV(Request $request)
	{
		$tenantId = $request->tenantId();

		$restaurant = Restaurant::find($tenantId);
		if (!$restaurant){
			return $this->response(['success' => false, 'error' => __eva('Invalid restaurant')]);
		}

		// Authorization
		$user = Eva::make('user');
		if (!$user->canExport('customers', $tenantId)){
			return $this->response([ 'success' => false, 'error' => __eva("You cannot export data.") ]);
		}

		// Get the list of ids to export
		$list_ids = $request->list;
		if (empty($list_ids)) {
			return $this->response(['success' => false, 'error' => __eva('No data to download.')]);
		}
		$list_ids = explode(',', $list_ids);

		$customers = Customer::whereIn('id', $list_ids)->orderBy('name', 'asc')->get();

		$list = [];
		foreach($customers as $customer){
			$list[] = $customer->toCsvArray();
		}
		if (empty($list)) {
			return $this->response(['success' => false, 'error' => __eva('No data to download.')]);
		}

		return $this->response(['success' => 'true', 'data' => $this->convertToCsv($list)]);
	}

	public function printPDF(Request $request)
	{
		$lang = $request->lang;
		$tenantId = $request->tenantId();

		$restaurant = Restaurant::find($tenantId);
		if (!$restaurant){
			return $this->response(['success' => false, 'error' => __eva('Invalid restaurant')]);
		}

		// Authorization
		$user = Eva::make('user');
		if (!$user->canExport('customers', $tenantId)){
			return $this->response([ 'success' => false, 'error' => __eva("You cannot print data.") ]);
		}

		// Get the list of ids to export
		$list_ids = $request->list;
		if (empty($list_ids)) {
			return $this->response(['success' => false, 'error' => __eva('No data to download.')]);
		}
		$list_ids = explode(',', $list_ids);

		// Prepare the booking
		$customers = Customer::whereIn('id', $list_ids)->orderBy('name', 'asc')->get();

		$html = __eva("This is the FREE version, you need to upgrade to the PRO version");
		if (defined("ALEXR_PRO_PLUGIN_DIR")){
			ob_start();
			include ALEXR_PRO_PLUGIN_DIR . 'includes-pro/dashboard/templates/pdf/customers.php';
			$html = ob_get_clean();
		}

		return $this->response(['success' => true, 'html' => $html]);
	}


	// IMPORT CUSTOMERS
	//===================================================================
	/**
	 * Import one user
	 * Sent from the import component CSV
	 *
	 * @param Request $request
	 *
	 * @return \WP_REST_Response
	 */
	public function import(Request $request)
	{
		// Check restaurant
		$tenantId = $request->tenantId();
		$restaurant = Restaurant::find($tenantId);
		if (!$restaurant){
			return $this->response(['success' => false, 'error' => __eva('Invalid restaurant')]);
		}

		// Check permissions
		$user = Eva::make('user');
		if (!$user->canImport('customers', $tenantId)){
			return $this->response([ 'success' => false, 'error' => __eva("You cannot import customers.") ]);
		}


		// Check email validation
		$email = $request->email;
		if (!$email) {
			return $this->response(['success' => false, 'error' => __eva('No email')]);
		}

		$email = trim($email);
		if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
			return $this->response(['success' => false, 'error' => __eva('Invalid email')]);
		}

		// Check it is not imported
		$customer = Customer::where(evavel_tenant_field(), $tenantId)->where('email', $email)->first();
		$already_exists = false;
		if ($customer) {
			$already_exists = true;
		}

		// Prepare the fields
		$params = $request->params;
		$first_name = isset($params['first_name']) ? $params['first_name'] : null;
		$last_name = isset($params['last_name']) ? $params['last_name'] : null;
		$dial_code = $this->map_dial_code($params);
		$phone = isset($params['phone']) ? $params['phone'] : null;
		$visits_imported =  isset($params['visits']) ? intval($params['visits']) : 0;
		$spend_imported =  isset($params['spend']) ? intval($params['spend']) : 0;
		$notes = isset($params['notes']) ? $params['notes'] : '';
		$language = $this->map_language($params);

		$tags_ids = $this->map_tags($params, $tenantId);

		$args = [
			'name' => $first_name ? $first_name.' '.$last_name : $last_name,
			'first_name' => $first_name,
			'last_name' => $last_name,
			'dial_code' => $dial_code,
			'phone' => $phone,
			'visits_imported' => $visits_imported,
			'spend_imported' => $spend_imported,
			'language' => $language,
			'notes' => $notes
		];

		// Update or create
		if ($already_exists) {
			Customer::where('id', $customer->id)->update($args);
		} else {
			$args[evavel_tenant_field()] = $tenantId;
			$args['email'] = $email;
			$customer = Customer::create($args);
		}
		$customer->calculateVisits();

		// Tags
		if (!empty($tags_ids)) {
			$customer->tags()->sync($tags_ids);
			$customer->save();
		}

		if ($already_exists) {
			return $this->response(['success' => true, 'is_new' => false, 'message' => __eva('Already exist')]);
		}
		return $this->response(['success' => true, 'is_new' => true, 'message' => __eva('Imported')]);
	}

	protected function map_dial_code($params)
	{
		$dial_code = isset($params['country_code']) ? $params['country_code'] : null;
		if ($dial_code) {
			$dial_code = preg_replace('/[^0-9]/', '', $dial_code);
		}
		return $dial_code;
	}

	protected function map_language($params)
	{
		$language = isset($params['language']) ? strtolower($params['language']) : null;
		$languages = evavel_languages_all();
		$found_language = false;
		foreach($languages as $key => $label) {
			if ($key == $language || strtolower($label) == $language){
				$language = $key;
				$found_language = true;
			}
		}
		if (!$found_language) {
			$language = null;
		}
		return $language;
	}

	protected function map_tags($params, $tenantId)
	{
		if (!isset($params['tags'])) return [];

		$tags = $params['tags'];
		if (empty($tags)) return [];

		$tags = explode(',', $tags);
		if (!is_array($tags)) return [];

		$tags = array_filter($tags, function($var){
			return ($var !== NULL && $var !== false && $var !== "");
		});
		if (count($tags) == 0) return [];

		// Comprobar que existe un grupo llamado Imported
		$tag_group = CTagGroup::where('name', 'Imported')
		                      ->where('restaurant_id', $tenantId)
		                      ->first();

		if (!$tag_group) {
			$tag_group = CTagGroup::create([
				'restaurant_id' => $tenantId,
				'name' => 'Imported',
				'ordering' => 999,
				'color' => '#FFFFFF',
				'backcolor' => '#000000',
				'is_deletable' => 1,
				'is_vip' => 0,
				'notes' => ''
			]);
		}

		// Y añadir las etiquetas ahí
		$tags_ids = [];

		foreach($tags as $tag) {

			$tag = trim($tag);

			$tag_model = CTag::where('name', $tag)
		                 ->where('group_id', $tag_group->id)
		                 ->first();

			if (!$tag_model) {
				$tag_model = CTag::create([
					'restaurant_id' => $tenantId,
					'group_id' => $tag_group->id,
					'name' => $tag,
					'ordering' => 999,
					'is_deletable' => 1,
					'notes' => ''
				]);
			}

			$tags_ids[] = $tag_model->id;
		}

		// Y devolver los ids
		return $tags_ids;
	}
}
