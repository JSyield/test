<?php

namespace Alexr\Http\Controllers;

use Alexr\Enums\BookingStatus;
use Alexr\Events\EventBookingCreated;
use Alexr\Events\EventBookingModified;
use Alexr\Events\EventBookingSeatsChanged;
use Alexr\Events\EventBookingStatusChanged;
use Alexr\Events\EventBookingTablesChanged;
use Alexr\Http\Traits\DownloadCsvController;
use Alexr\Http\Traits\GeneralMetricsController;
use Alexr\Http\Traits\SendEmailsController;
use Alexr\Http\Traits\SendSmsController;
use Alexr\Http\Traits\ShiftMetricsController;
use Alexr\Http\Traits\BookingsUsePaymentsController;
use Alexr\Http\Traits\WalkinBookingController;
use Alexr\Listeners\ListenBookingEvents;
use Alexr\Models\Booking;
use Alexr\Models\Customer;
use Alexr\Models\Restaurant;
use Alexr\Models\Traits\CsvHelpers;
use Alexr\Settings\Event;
use Alexr\Settings\Shift;
//use Carbon\Carbon;
use Evavel\Eva;
use Evavel\Http\Controllers\Controller;
use Evavel\Http\Request\Request;
use Evavel\Query\Query;
use Evavel\Support\Str;
use http\Exception;

class BookingsController extends Controller
{
	use CsvHelpers,
		SendEmailsController,
		SendSmsController,
		DownloadCsvController,
		ShiftMetricsController,
		GeneralMetricsController,
		BookingsUsePaymentsController;

	protected $sendEmail = false;

	public function indexStatus(Request $request)
	{
		//ray('GET BOOKINGS PENDING');
		//ray($request->params);

		return $this->index($request);
	}

	public function index(Request $request)
	{
		$user = Eva::make('user');
		if (!$user->canManage($request->tenant)){
			return $this->response([ 'success' => false, 'error' => __eva("You cannot access.") ]);
		}

		// Date can be:
		// 2022-09-03 for a specific day
		// 2022-09 for a month
		// 2022-01-01-2022-12-01 for a range of dates

		$date = $request->date;
		$tenantId = $request->tenantId();

		// Removing holded bookings
		// Using scheduler action now every X minutes
		//alexr_remove_holded_bookings($tenantId);

		// If no perPage parameter then request all
		$perPage = isset($request->params['per_page']) ? $request->params['per_page'] : 100000;
		$currentPage = isset($request->params['page']) ? $request->params['page'] : 1;
		$search = isset($request->params['search']) ? $request->params['search'] : null;

		$orderBy = isset($request->params['order']) ? $request->params['order'] : 'time';
		$orderBy = empty($orderBy) ? 'time' : $orderBy;

		$orderByDirection = isset($request->params['direction']) ? $request->params['direction'] : 'asc';
		$orderByDirection = empty($orderByDirection) ? 'asc' : $orderByDirection;

		// Retrieve all statuses except when it is in mode selected
		// that is when the user is making the reservation online

		$year_month = '';

		// One day
		if (preg_match('#^(\d{4}-\d{2})-\d{2}$#', $date, $matches))
		{
			[$query, $query_count, $year_month] = $this->getQueriesForOneDay($date, $tenantId, $matches);
		}

		// One month
		else if (preg_match('#^(\d{4}-\d{2})$#', $date, $matches))
		{
			[$query, $query_count, $year_month] = $this->getQueriesForOneMonth($date, $tenantId, $matches);

			// I want all because it is used by the calendar
			$currentPage = 1;
			$perPage = 10000;
		}

		// Range dates
		else if (preg_match('#^(\d{4}-\d{2}-\d{2})-(\d{4}-\d{2}-\d{2})$#', $date, $matches))
		{
			[$query, $query_count, $year_month] = $this->getQueriesForRangeDates($tenantId, $matches);
		}

		// Es una consulta de las reservas pending
		else if ($date == 'all') {
			[$query, $query_count, $year_month] = $this->getQueriesFromToday($tenantId);
		}

		// Reservas pending
		if (isset($request->params['status'])) {
			$query->where('status', $request->params['status']);
			$query_count->where('status', $request->params['status']);
		}


		// Filter search
		if ($search) {
			$this->applySearch($search, $query_count );
			$this->applySearch($search, $query);
		}


		// Do the query
		//ray('QUERY BOOKINGS');
		//Query::setDebug(true);
		$total_arr = $query_count->count($perPage);
		//Query::setDebug(false);
		//ray('END QUERY BOOKINGS');
		//ray($total_arr);
		//$bookings = $query->orderBy('time', 'asc')->get()->toArrayQuery::setDebug(true);

		//Query::setDebug(true);
		$bookings = $query->orderBy($orderBy, $orderByDirection)
		                  ->page($currentPage, $perPage)
		                  ->get()
		                  ->toArray();
		//ray(count($bookings));
		//Query::setDebug(false);

		// Get Bookings month IDs (helper max bookings per month)
		$bookings_year_month = $this->getBookingsYearMonth($tenantId, $year_month);

		return $this->response([
			'success' => true,
			'bookings' => $bookings,
			'yearMonthEditing' => $year_month,
			'total' => $total_arr['count'],
			'total_pages' => $total_arr['pages'],
			'per_page' => $perPage,
			'page' => $currentPage,
			'bookingsIdMonth' => $bookings_year_month
		]);
	}

	protected function getQueriesFromToday($tenantId)
	{
		$date_now = evavel_date_now()->format('Y-m-d');

		$query = Booking::where('restaurant_id', $tenantId)
		                ->with('customer')
		                ->where('date', '>=', $date_now)
		                ->where('status', '!=', BookingStatus::SELECTED)->where('status', '!=', BookingStatus::DELETED);

		$query_count = Booking::where('restaurant_id', $tenantId)
                      ->with('customer')
                      ->where('date', '>=', $date_now)
                      ->where('status', '!=', BookingStatus::SELECTED)->where('status', '!=', BookingStatus::DELETED);


		return [$query, $query_count, $date_now];
	}

	protected function getQueriesForOneDay($date, $tenantId, $matches)
	{
		$query = Booking::where('restaurant_id', $tenantId)
		                ->with('customer')
		                ->where('date', $date)
		                ->where('status', '!=', BookingStatus::SELECTED)->where('status', '!=', BookingStatus::DELETED);

		$query_count = Booking::where('restaurant_id', $tenantId)
		                ->with('customer')
		                ->where('date', $date)
		                ->where('status', '!=', BookingStatus::SELECTED)->where('status', '!=', BookingStatus::DELETED);

		return [$query, $query_count, $matches[1]];
	}

	protected function getQueriesForOneMonth($date, $tenantId, $matches)
	{
		$query = Booking::where('restaurant_id', $tenantId)
		                ->with('customer')
		                ->where('date', 'like', $date)
		                ->where('status', '!=', BookingStatus::SELECTED)->where('status', '!=', BookingStatus::DELETED);

		$query_count = Booking::where('restaurant_id', $tenantId)
		                ->with('customer')
		                ->where('date', 'like', $date)
		                ->where('status', '!=', BookingStatus::SELECTED)->where('status', '!=', BookingStatus::DELETED);

		return [$query, $query_count, $matches[1]];
	}

	protected function getQueriesForRangeDates($tenantId, $matches)
	{
		$date1 = $matches[1];
		$date2 = $matches[2];

		$query = Booking::where('restaurant_id', $tenantId)
		                ->with('customer')
		                ->where('date', '>=', $date1)
		                ->where('date', '<=', $date2)
		                ->where('status', '!=', BookingStatus::SELECTED)->where('status', '!=', BookingStatus::DELETED);

		$query_count = Booking::where('restaurant_id', $tenantId)
		                ->with('customer')
		                ->where('date', '>=', $date1)
		                ->where('date', '<=', $date2)
		                ->where('status', '!=', BookingStatus::SELECTED)->where('status', '!=', BookingStatus::DELETED);

		// Calculate year month
		// I need to know the number of dates of the interval
		// > 10 days means asking for a month
		// < 10 days means asking for a week
		// I have to calculate the correct month I want to fetch
		//$diff_days = Carbon::createFromFormat('Y-m-d', $date2)
		//                   ->diff(Carbon::createFromFormat('Y-m-d', $date1))->days;

		$diff_days = evavel_date_createFromFormat('Y-m-d', $date2)
			->diff(evavel_date_createFromFormat('Y-m-d', $date1))->days;

		// When fetching a month I ask for some days before and after current month
		// so adding 15 days will make sure it is the corrent month
		if ($diff_days < 10)
		{
			$year_month = substr($date1, 0, 7);
		}
		else
		{
			/*$year_month = Carbon::createFromFormat('Y-m-d', $date1)
			                    ->addDays(15)
			                    ->format('Y-m');*/

			$year_month = evavel_date_createFromFormat('Y-m-d', $date1)
			                    ->addDays(15)
			                    ->format('Y-m');
		}

		return [$query, $query_count, $year_month];
	}

	protected function getBookingsYearMonth($tenantId, $year_month)
	{
		$bookings_year_month = Booking::where('restaurant_id', $tenantId)
		                              ->where('date', 'like', $year_month)
		                              ->where('status', '!=', BookingStatus::SELECTED)
		                              ->where('status', '!=', BookingStatus::DELETED)
		                              ->orderBy('date', 'asc')
		                              ->get()
		                              ->map(function($booking){ return intval($booking->id); })
		                              ->toArray();
		return $bookings_year_month;
	}

	// Booking data
	public function booking(Request $request)
	{
		$id = intval($request->bookingId);

		$booking = Booking::find($id);

		if ($booking == null){
			return $this->response([ 'success' => false, 'error' => __eva("No booking found.") ]);
		}

		$user = Eva::make('user');
		if (!$user->canManage($booking->restaurant_id)){
			return $this->response([ 'success' => false, 'error' => __eva("You cannot access.") ]);
		}

		return $this->response([
			'booking' => $booking,
			'notifications' => $booking->notifications->toArray(),
			'payments' => $booking->payments->toArray(),
			'actions' => $booking->actions->toArray()
		]);
	}

	public function create(Request $request)
	{
		$user = Eva::make('user');
		$restaurant_id = $request->tenant;

		if (!$user->canCreate('bookings', $request->tenant)) {
			return $this->response([ 'success' => false, 'error' => __eva("You cannot create bookings.") ]);
		}

		//ray('Create new booking');
		$booking = new Booking();
		$booking->restaurant_id = $restaurant_id;

		$this->sendEmail = $request->sendEmail == 'yes' ? true : false;

		$result = $this->updateBookingData($request, $booking, true);

		evavel_event(new EventBookingCreated($booking, $user));

		return $result;
	}

	public function update(Request $request)
	{
		//ray('UPDATE');
		$user = Eva::make('user');

		if (!$user->canEdit('bookings', $request->tenant)){
			return $this->response([ 'success' => false, 'error' => __eva("You cannot edit this booking [update].") ]);
		}

		$booking = Booking::find($request->bookingId);

		$this->sendEmail = $request->sendEmail == 'yes' ? true : false;

		$booking_original = ListenBookingEvents::getOriginal($booking);

		// Booking drag&drop from the timeline view
		if ($request->editMode == 'update-timeline-view')
		{
			$result = $this->updateBookingDataFromTimelineView($request, $booking);
		}
		// Full update
		else
		{
			$result = $this->updateBookingData($request, $booking, false);
		}

		evavel_event(new EventBookingModified($booking, $user, $booking_original));

		return $result;
	}

	protected function updateBookingDataFromTimelineView(Request $request, $booking)
	{
		$params = $request->body_params;
		$tables = json_decode($params['tables']);

		$booking->time = intval($params['time']);
		$booking->duration = intval($params['duration']);
		$booking->save();

		$booking->syncTablesAndKeepCurrentSeats($tables);

		return $this->response([ 'success' => true, 'booking' => $booking ]);
	}

	protected function updateBookingData(Request $request, $booking, $is_new = true)
	{
		$restaurant_id = $request->tenant;
		$user = Eva::make('user');

		//if (!$user->canManage($booking->restaurant_id)){
		//	return $this->response([ 'success' => false, 'error' => __eva("You cannot access.") ]);
		//}

		$params = $request->body_params;

		$tables = json_decode($params['tables']);
		$tags = json_decode($params['tags']);
		//$date = (new Carbon($params['date']))->format('Y-m-d');
		$date = evavel_new_date($params['date'])->format('Y-m-d');

		$status = $params['status'];

		if ($is_new){
			// @TODO: a way to define the source of the booking
			$booking->source = null;
			if (empty($params['type'])){
				$booking->type = 'in-house';
			}
		}

		// Check service exists
		$serviceId = intval($params['shift']);
		$service_name = 'Unknown';
		$service = \Alexr\Settings\Shift::where('id', $serviceId)->first();
		if (!$service) {
			$service = \Alexr\Settings\Event::where('id', $serviceId)->first();
		}
		if ($service){
			$service_name = $service->name;
		}

		$booking->restaurant_id = $request->tenant;

		$language = isset($params['language']) ? $params['language'] : false;
		if ($language) {
			$booking->language = $language;
		} else if (!$booking->language) {
			$booking->language = $booking->restaurant->language;
		}
		$booking->status = $status;
		$booking->date = $date;
		$booking->time = intval($params['slot']);
		$booking->party = intval($params['party']);
		$booking->duration = intval($params['duration']);
		$booking->shift_event_id = $serviceId;
		$booking->shift_event_name = $service_name;
		$booking->notes = $params['notes'];
		$booking->private_notes = $params['private_notes'];
		$booking->spend = intval($params['spend']);
		$booking->type = $params['type'];
		$booking->blocked_table = isset($params['blocked_table']) ? $params['blocked_table'] : 0;

		// Some emails have the format of walkin walkin_2023_12_14@fake
		$email = $params['email'];
		if (!$email || empty($email)) {
			$name = $params['first_name'];
			if (!empty($name)) {
				$name = Str::slug($name);
			} else {
				$name = 'user';
			}
			$email = $name.'_'.time().'@fake';
		}

		$booking->customer_id = intval($params['customer_id']) > 0 ?
			intval($params['customer_id']) :
			$this->findCustomerId($request->tenant, $email);

		$first_name = $params['first_name'];
		$last_name = $params['last_name'];
		if (empty($first_name) && empty($last_name)){
			$first_name = 'New Client';
			if ($status == BookingStatus::WAIT_LIST){
				$first_name = 'Wait-List';
			}
		}

		$booking->first_name = $first_name;
		$booking->last_name = $last_name;
		$booking->email = $email;
		$booking->phone = $params['phone'];
		$booking->country_code = $params['country_code'];
		$booking->dial_code = $params['dial_code'];
		$booking->dial_code_country = $params['dial_code_country'];

		$booking->agree_receive_email = intval($params['agree_receive_email']);
		$booking->agree_receive_sms = intval($params['agree_receive_sms']);
		$booking->sms_status = $params['sms_status'];
		if ($booking->sms_status == 'null') {
			$booking->sms_status = null;
		}

		// Custom fields
		$custom_fields = null;
		if (isset($params['custom_fields'])) {
			try {
				$custom_fields = json_decode($params['custom_fields'], true);
			} catch (\Exception $e){
				$custom_fields = null;
			}
		}

		$booking->custom_fields = $custom_fields;


		// Create a new customer
		$is_new_customer = false;
		if ($booking->customer_id == null) {
			$customer = $this->createNewCustomer($booking);
			if ($customer){
				$booking->customer_id = $customer->id;
				$is_new_customer = true;
			}
		}

		$booking->save();
		$booking->tags()->sync($tags);

		// Borra las sillas, no me interesa
		//$booking->tables()->sync($tables);

		// Borra las mesas que no esten en la lista y añade las nuevas
		// El resto las mantiene como estan
		$booking->syncTablesAndKeepCurrentSeats($tables);

		$spend_has_changed = false;

		// Recalculate visits to add 1 visit when new customer
		// Or just recalculate because spend has changed
		if ($is_new_customer || $spend_has_changed) {
			$customer->calculateVisits();
		}

		// Send email if requested
		if ($this->sendEmail){
			$this->sendBookingEmail($booking);
			$this->sendSmsNotification($booking);
		}

		return $this->response([ 'success' => true, 'booking' => $booking ]);
	}

	protected function applySearch($search, &$query) {
		if ($search) {
			$search_word = ''.$search;
			$query->where(function($query) use($search_word){
				$query->where('first_name', 'like', $search_word)
				      ->orWhere('last_name', 'like', $search_word)
				      ->orWhere('phone', 'like', $search_word)
				      ->orWhere('email', 'like', $search_word);
			});
		}
	}

	protected function findCustomerId($tenantId, $email) {
		if (empty($email) || !$email) {
			return null;
		}
		$customer = Customer::where('restaurant_id', $tenantId)->where('email', $email)->first();
		return $customer ? $customer->id : null;
	}

	public function sendBookingEmail($booking)
	{
		$booking->sendBookingEmail();
	}

	public function sendSmsNotification($booking)
	{
		$booking->sendSmsNotification();
	}

	public function updateStatus(Request $request)
	{
		$user = Eva::make('user');
		if (!$user->canEdit('bookings', $request->tenant)){
			return $this->response([ 'success' => false, 'error' => __eva("You cannot edit this booking [updateStatus].") ]);
		}

		$booking = Booking::find($request->params['bookingId']);
		if ($booking)
		{
			$old_status = $booking->status;
			$new_status = $request->params['status'];
			$booking->status = $new_status;
			$booking->save();

			evavel_event(new EventBookingStatusChanged($booking, $old_status, $new_status, $user));
		}

		return $this->response(['success' => true, 'booking' => $booking]);
	}

	protected function haveSeatsChanged($old_tables_seats, $new_tables_seats) {
		// Comparar para ver si han cambiado las sillas
		// Tengo que quitar las mesas sin sillas para poder comparar
		/*
		array [
		  304 => []
		  355 => [
		    0 => "4"
		    1 => "3"
		    2 => "6"
		    3 => "7"
		  ]
		  362 => [
		    0 => "5"
		    1 => "4"
		  ]
		]
		*/
		//ray($old_tables_seats);
		//ray($new_tables_seats);

		$list_old_seats = [];
		$list_new_seats = [];

		foreach ($old_tables_seats as $tableId => $seats) {
			if (!empty($seats)) {
				foreach ($seats as $seatId) {
					$list_old_seats[] = $tableId.'-'.$seatId;
				}
			}
		}

		foreach ($new_tables_seats as $tableId => $seats) {
			if (!empty($seats)) {
				foreach ($seats as $seatId) {
					$list_new_seats[] = $tableId.'-'.$seatId;
				}
			}
		}
		//ray($list_old_seats);
		//ray($list_new_seats);

		return count($list_new_seats) != count($list_old_seats);
	}

	public function updateTables(Request $request)
	{
		//ray('TENANT: ' . $request->tenant);
		//ray($request->tablesList);
		//ray($request->tablesSeatsList);
		//ray($request->params['bookingId']);

		$user = Eva::make('user');
		if (!$user->canEdit('bookings', $request->tenant)){
			return $this->response([ 'success' => false, 'error' => __eva("You cannot edit this booking [updateTables].") ]);
		}

		$booking = Booking::find($request->params['bookingId']);

		if ($booking)
		{
			$old_tables = $booking->tablesList;
			$new_tables = $request->tablesList;

			$old_tables_seats = $booking->tablesSeatsList;
			$new_tables_seats = $request->tablesSeatsList;

			$seats_have_changed = $this->haveSeatsChanged($old_tables_seats, $new_tables_seats);

			// Asegurarse que los seats tienen todas las mesas antes de llamar a sync
			foreach ($new_tables as $tableId) {
				if (!isset($new_tables_seats[$tableId])) {
					$new_tables_seats[$tableId] = [];
				}
			}

			// Ajustar los arrays de las sillas a un string en el pivot
			$new_tables_seats_adjusted = [];
			foreach($new_tables_seats as $key => $value_arr) {
				$new_tables_seats_adjusted[$key] = ['seats' => implode(',', $value_arr)];
			}

			//$booking->tables()->sync($new_tables);
			$booking->tables()->sync($new_tables_seats_adjusted);

			// Seats han cambiado
			if ($seats_have_changed) {
				//ray('SEATS CHANGED');
				evavel_event(new EventBookingSeatsChanged($booking, $old_tables_seats, $new_tables_seats, $user));
			}
			// Mesas que han cambiado
			else {
				//ray('TABLES CHANGED');
				evavel_event(new EventBookingTablesChanged($booking, $old_tables, $new_tables, $user));
			}


		}

		return $this->response(['success' => true, 'booking' => $booking]);
	}


	protected function createNewCustomer($booking) {

		return alexr_create_new_customer($booking);
	}

	/**
	 * Get covers allowed for shift and date
	 *
	 * @param Request $request
	 *
	 * @return \WP_REST_Response
	 */
	public function covers(Request $request)
	{
		$shiftId = $request->shiftId;
		$date = $request->date;
		//ray($shiftId.' - '.$date);

		$shift = Shift::where('id', $shiftId)->first();
		if (!$shift) {
			$shift = Event::where('id', $shiftId)->first();
		}
		if (!$shift) {
			return $this->response(['covers' => []]);
		}

		//$covers_intervals = $shift->mapTimeSlotCovers($date, false);
		//ray($covers_intervals);

		$covers_intervals_with_new = $shift->mapTimeSlotCovers_WithNewCovers($date);
		//ray($covers_intervals_with_new);


		// Returns array
		// [ "46800-47700" => 4, "47700-48600" => 4 ]
		// Simplify to [ 46800 => 4, 47700 => 4 ]
		$covers_total = [];
		$covers_new = [];
		foreach($covers_intervals_with_new as $interval => $data)
		{
			$explode = explode('-', $interval);
			if (is_array($explode) && count($explode) == 2)
			{
				$covers_total[$explode[0]] = $data['total'];
				$covers_new[$explode[0]] = $data['new'];
			}
		}

		// Tengo que transformar los news y acumularlos segun los intervalos del shift, no cada 15 minutos
		$covers_new_shift = [];
		//ray($shift->interval);
		for($time = $shift->first_seating; $time <= $shift->last_seating; $time += $shift->interval)
		{
			$time_start = $time;
			$time_end = $time + $shift->interval;
			$covers_new_shift[$time] = 0;

			for ($time2 = $time_start; $time2 < $time_end; $time2 += 900) {
				if (isset($covers_new[$time2])) {
					$covers_new_shift[$time] += $covers_new[$time2];
				}
			}

			//ray('NEW COVERS ENTRE ' . $time_start . ' - ' + $time_end . ' = ' . $covers_new_shift[$time]);

		}

		//ray($covers_total);
		//ray($covers_new);
		//ray($covers_new_shift);
		return $this->response(['covers' => $covers_total, 'covers_new' => $covers_new, 'covers_new_shift' => $covers_new_shift]);
	}

	/**
	 * Search for reserved tables from a selected date, shift, slot, duration
	 * Useful for the reservation modal
	 *
	 * @param Request $request
	 *
	 * @return \WP_REST_Response
	 */
	public function tables(Request $request)
	{
		$shiftId = intval($request->shiftId);
		$date = $request->date;
		$slot = intval($request->slot);
		$duration = intval($request->duration);

		$shift = Shift::where('id', $shiftId)->first();
		if (!$shift) {
			$shift = Event::where('id', $shiftId)->first();
		}
		if (!$shift) {
			return $this->response(['tables' => []]);
		}

		//ray([ 'shiftId' => $shiftId, 'date' => $date, 'slot' => $slot, 'duration' => $duration ]);

		$tables = $shift->getListTablesIdOccupied($date, $slot, $duration);

		$restaurant = Restaurant::find($shift->restaurant_id);
		$tables_blocked = [];
		if ($restaurant){
			$tables_id_blocked_start = $restaurant->getBlockedTables($date, $slot);
			$tables_id_blocked_end = $restaurant->getBlockedTables($date, $slot+$duration);
			$tables_blocked = array_merge($tables_id_blocked_start, $tables_id_blocked_end);
			$tables_blocked = array_unique($tables_blocked);
		}
		//ray($tables_blocked);

		return $this->response([
			'tables' => array_values($tables), // tables reserved
			'tables_blocked' => array_values($tables_blocked)
		]);
	}

	// Metrics

}
