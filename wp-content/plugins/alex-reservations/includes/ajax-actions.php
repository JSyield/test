<?php

use Alexr\Listeners\ListenBookingEvents;
use Alexr\Models\Booking;
use Alexr\Models\BTag;
use Alexr\Models\BTagGroup;

if ( ! defined( 'ABSPATH' ) ) exit;

use Alexr\Enums\BookingStatus;

class ALEXR_Ajax_Actions {

	use Alexr_ajax_payment_actions;
	use Alexr_ajax_stripe_actions;
	use Alexr_ajax_redsys_actions;
	use Alexr_ajax_paypal_actions;
	use Alexr_ajax_mercadopago_actions;
	use Alexr_ajax_mollie_actions;
	use Alexr_ajax_square_actions;
	use Alexr_ajax_floorplan_actions;

	//use Alexr_ajax_api;


	public function __construct() {
		$this->loadAjaxActions();
	}

	protected function loadAjaxActions()
	{
		$hooks = [
			'rr_get_restaurant_data', // Get initial data
			//'rr_search_bookings', // Get slots bases on guests, date and time. OLD
			'rr_get_service_slots', // Get slots based on shift/event selected
			'rr_hold_booking',
			'rr_hold_booking_remove',
			'rr_get_fields',
			'rr_get_booking_tags',

			'rr_make_booking', // finally procceed with the booking

			'rr_cancel_booking', // From view booking actions
			'rr_confirm_booking', // From view booking user confirms
			'rr_get_booking', // Used for the view booking front page
			'rr_modify_booking', // Modify existing booking

			// Stripe payment
			'rr_stripe_create', // Generate intent payment secretClient
			'rr_stripe_payment_confirmed',
			'rr_stripe_save_token', // para pre-autorizar un pago

			// ViewBooking
			'rr_get_payment_details', // Get the payment details for a booking, amonunt, currenct
			'rr_get_stripe_receipt', // Get the url of the receipt from the intent id

			'rr_update_booking_status', // Email actions

			// For selecting areas and tables
			'rr_get_floorplan',
			'rr_get_floorplan_with_panoramas',

			// Redsys
			'rr_redsys_create', // Generate request for redsys
			'rr_redsys_fetch', // Get previos parameters for the booking view

			// Paypal
			'rr_paypal_create', // Save payment request with result after the Paypal response
			'rr_paypal_save_card',
			'rr_paypal_pre_auth',
			'rr_paypal_fetch', // BookingView. Get data to prepare the payment to pay pending Paypal

			// Mercadopago
			'rr_mercadopago_create', // Create payment and generate Preference for mercado pago with the url to redirect
			'rr_mercadopago_fetch', // Get url to redirect again

			// Mollie
			'rr_mollie_create', // Generate the link for payment
			'rr_mollie_fetch', // Viewbooking -> get the link to try payment

			// Square
			'rr_square_create',
			'rr_square_fetch',

			// Webhooks
			'rr_webhook', // @TODO PENDING mercadopago

			// Voice API
			//'rr_api_get_options_for_date',
		];

		foreach ($hooks as $action){
			$func = str_replace('rr_', '', $action);
			add_action( 'wp_ajax_'.$action, array($this, $func));
			add_action( 'wp_ajax_nopriv_'.$action, array($this, $func));
		}
	}

	/**
	 * Wrapper for the wp verify nonce
	 *
	 * @param $restaurant_id
	 *
	 * @return false|int
	 */
	protected function verify_nonce($restaurant_id)
	{
		//ray('verify nonce ' . $restaurant_id);
		$nonce = sanitize_text_field( $_REQUEST['nonce'] );
		return evavel_tenant_verify_nonce($nonce, $restaurant_id);
	}

	/**
	 * STEP 1 - Data needed for the first step
	 *
	 * @return void
	 */
	public function get_restaurant_data()
	{
		$restaurant_id = sanitize_text_field($_REQUEST['restaurantId']);

		if ( ! $this->verify_nonce($restaurant_id) ) {
			wp_send_json_error(['error' => __eva('Cannot open the widget. Invalid nonce. Try to refresh the page.')]);
		}

		$widget_id = sanitize_text_field($_REQUEST['widgetId']);

		$restaurant = \Alexr\Models\Restaurant::where('active', 1)->where('id', $restaurant_id)->first();
		if (!$restaurant) {
			wp_send_json_error(['error' => __eva('Restaurant is not available.')]);
		}

		// ++++ I need to remove HOLDED booking before calculating the slots available
		// Removed selected bookings with more than X minutes holded
		//alexr_remove_holded_bookings($restaurant_id);

		// This way does not generate default fields
		//$widget_form = \Alexr\Settings\WidgetForm::where('restaurant_id', $restaurant->id)->first();
		//$form_config = $widget_form->form_config;
		//$form_fields = $widget_form->form_fields;

		// Check widget exists
		$widget_form = \Alexr\Settings\WidgetForm::where('id', $widget_id)->first();
		if (!$widget_form) {
			wp_send_json_error(['error' => __eva('Form is not available.')]);
		}
		$show_services_dropdown = $widget_form->form_config['show_services_dropdown'];
		$show_services_dropdown = $show_services_dropdown == 'no' ? false : true;

		$show_services_duration = isset($widget_form->form_config['show_services_duration']) ? $widget_form->form_config['show_services_duration'] : 'yes';
		$show_services_duration = $show_services_duration == 'no' ? false : true;

		$show_not_available_slots = isset($widget_form->form_config['show_not_available_slots']) ? $widget_form->form_config['show_not_available_slots'] : 'no';
		$show_not_available_slots = $show_not_available_slots == 'no' ? false : true;

		// This generates default values if empty
		$items = \Alexr\Settings\WidgetForm::getItems(null, $restaurant_id, $widget_id);
		$form_config = $items['form_config'];
		$form_fields = $items['form_fields'];

		$guests_range = $restaurant->guestsMinMax($widget_id);

		if ($restaurant) {

			//$daysAvailable = ['2022-11-22', '2022-11-23', '2022-11-24'];
			//$daysAvailable = $restaurant->availableDates($widget_id);

			// Also filter available dates by closed days setting
			$daysAvailableWithShifts = $restaurant->availableDates($widget_id, true);
			$daysAvailable = array_keys($daysAvailableWithShifts);

			wp_send_json_success([
				'restaurant' => $restaurant->toArray(),
				'config' => [
					'guests_min' => $guests_range['min'],
					'guests_max' => $guests_range['max'],
					'time_slots' => $restaurant->timeSlotsBookable($widget_id),
					'services' => $restaurant->getServices($widget_id),
					'show_services_dropdown' => $show_services_dropdown,
					'show_services_duration' => $show_services_duration,
					'show_not_available_slots' => $show_not_available_slots,

					'base_color' => $form_config['base_color'],
					'text_color' => $form_config['text_color'],

					'link_terms_of_service' => isset($form_config['link_terms_of_service']) ? $form_config['link_terms_of_service'] : '/',
					'link_privacy_policy' => isset($form_config['link_privacy_policy']) ? $form_config['link_privacy_policy'] : '/',

					'isBookable' => count($daysAvailable) > 0,
					'daysAvailable' => $daysAvailable,
					'daysAvailableWithShifts' => $daysAvailableWithShifts,

					// @TODO PENDING
					'closedDates' => [],

					'events' => $restaurant->getEventsToDisplayInCalendar($widget_id, $daysAvailable),

					'header_text' => isset($form_config['header_text']) ? $form_config['header_text'] : '',

					'language_default' => $form_config['language_default'],
					'languages_allowed' => $form_config['languages_allowed'],
					'date_formats' => alexr_config('app.date_formats')
				]
			]);
		}

		wp_send_json_error(['error' => __eva('Restaurant does not exists.')]);
	}

	/**
	 * STEP 2 - Request allowed bookings
	 * Search based on Guests, Date, Time
	 * OLD WAY - now is showing all Shift slots available
	 * @return void
	 */
	public function search_bookings()
	{
		// In case restaurant is not active or all hours are blocked
		//wp_send_json_error(['message' => 'No bookings are allowed at the moment.']);

		$restaurant_id = sanitize_text_field($_REQUEST['restaurantId']);

		// @TODO. pending nonce
		$widget_id = sanitize_text_field($_REQUEST['widgetId']);

		$restaurant = \Alexr\Models\Restaurant::where('active', 1)->where('id', $restaurant_id)->first();

		if (!$restaurant) {
			wp_send_json_error(['message' => __eva('Restaurant is not active.')]);
		}

		$guests = intval($_REQUEST['guests']);
		$date = sanitize_text_field($_REQUEST['date']);
		$time = intval($_REQUEST['time']);

		$result = [
			'request' => [
				'tenant'    => $restaurant_id,
				'guests'    => $guests,
				'date'      => $date,
				'time'      => $time
			],
			'resultDate' => [
				'date' => $date,
				'slots' => $restaurant->getSlotsAvailable($guests, $date, $time, $widget_id),
			],
			'resultOtherDates' => $restaurant->getOtherDaysSlotsAvailable($guests, $date, $time, $widget_id),
		];

		wp_send_json_success($result);
	}

	/**
	 * Get available slots for a service
	 *
	 * @return void
	 */
	public function get_service_slots()
	{
		$restaurant_id = intval($_REQUEST['restaurantId']);

		if ( ! $this->verify_nonce($restaurant_id) ) {
			wp_send_json_error(['message' => __eva('Invalid nonce. Forbidden to access data.')]);
		}

		// Check restaurant exists
		$restaurant = \Alexr\Models\Restaurant::where('active', 1)->where('id', $restaurant_id)->first();
		if (!$restaurant) {
			wp_send_json_error(['message' => __eva('Restaurant is not active.')]);
		}

		// Check widget exists
		$widget_id = intval($_REQUEST['widgetId']);
		$w_form = \Alexr\Settings\WidgetForm::where('id', $widget_id)->first();
		if (!$w_form) {
			wp_send_json_error(['message' => __eva('Widget not found.')]);
		}

		// Get the date and guests
		$date = sanitize_text_field($_REQUEST['date']);
		$guests = intval($_REQUEST['guests']);


		// Check service exists if is not asking for all services
		// if service_id = all means to search in all services the available slots
		$service_id = sanitize_text_field($_REQUEST['serviceId']);
		if ($service_id != 'all'){
			$service_id = intval($service_id);
			$service = alexr_get_service($service_id);
			if (!$service) {
				wp_send_json_error(['message' => __eva('Service not found.')]);
			}
		}

		$days_in_advance = $w_form->form_config['max_days_in_advance'];

		// Search for 1 service or for all services
		//ray('TOTAL RESULTS FOR  ' . $service_id . ' for guests ' . $guests);
		if ($service_id != 'all') {
			$result = $this->get_service_slots_OneService($service, $guests, $date, $restaurant, $days_in_advance);
			//ray('PARTIAL RESULTS FOR SERVICE ' . $service->id . ' for guests ' . $guests);
			//ray($result);
		}
		else {
			$result = $this->get_service_slots_AllServices($guests, $date, $restaurant, $days_in_advance, $widget_id);
		}

		// If date is closed has no slots available
		if ($restaurant->isDateClosed($date)){
			$result['resultDate']['slots'] = [];
		}

		// Add the request to the result
		$result['request'] = [
			'restaurantId' => $restaurant_id,
			'guests'       => $guests,
			'date'         => $date,
			'widgetId'     => $widget_id,
			'serviceId'    => $service_id
		];

		$result['bookingsIdMonth'] = $this->getBookingsYearMonth($restaurant_id, substr($date, 0, 7));

		$result['message_not_available'] = $w_form->getMessageSlotsNotAvailable();

		wp_send_json_success($result);

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

	protected function get_service_slots_OneService($service, $guests, $date, $restaurant, $days_in_advance)
	{
		//ray('get_service_slots_OneService: '.$service->id.' PAX: '.$guests.' DATE: '.$date.' RET: '.$restaurant->id.' ADV: ' . $days_in_advance );

		// Slots available
		$list_slots = [];
		$available_slots = $service->getAllSlotsAvailable($guests, $date);
		//ray($available_slots);
		$all_slots = $service->getAllBookableSlots($date);
		//ray($available_slots);
		//ray($all_slots);

		// Additional data to slot
		$duration = $service->getDuration($guests);

		foreach($all_slots as $slot)
		//foreach($available_slots as $slot)
		{
			$list_slots[] = [
				'available' => in_array($slot, $available_slots),
				'name' => $service->name,
				'translations' => $service->translations,
				'id' => $service->id,
				'date' => $date,
				'time' => $slot,
				'duration' => $duration,
				'backcolor' => $service->color,
				'textcolor' => $service->color_text
			];
		}
		//ray($list_slots);

		/*foreach($slots as $slot) {
			$list_slots[] = [
				'name' => $service->name,
				'translations' => $service->translations,
				'id' => $service->id,
				'date' => $date,
				'time' => $slot,
				'duration' => $duration,
				'backcolor' => $service->color,
				'textcolor' => $service->color_text
			];
		}*/

		// Check service has not been blocked
		if (!$restaurant->isOnlineBookingsEnabled($service->id, $date)){
			$list_slots = [];
		}

		// Days available
		$daysAvailable = $service->availableDates($restaurant->timezone, $days_in_advance);

		$result = [
			'guests_min' => $service->min_covers_reservation,
			'guests_max' => $service->max_covers_reservation,
			'daysAvailable' => $daysAvailable,
			'resultDate' => [
				'date' => $date,
				'slots' => $list_slots
			],
			//'resultOtherDates' => [] // @TODO PENDING
		];

		return $result;
	}

	protected function get_service_slots_AllServices($guests, $date, $restaurant, $days_in_advance, $widget_id)
	{
		$services = $restaurant->getShiftsAndEvents($widget_id);
		$result = [];

		$list_results = [];
		foreach($services as $service) {
			$results = $this->get_service_slots_OneService($service, $guests, $date, $restaurant, $days_in_advance);
			$list_results[] = $results;
		}

		// Merge all results: guests_min, guests_max, daysAvailable, resultDate[slots]
		$result['guests_min'] = 1000;
		$result['guests_max'] = 1;
		$result['daysAvailable'] = [];
		$result['resultDate'] = [
			'date' => $date,
			'slots' => []
		];

		foreach($list_results as $item)
		{
			if ($item['guests_min'] < $result['guests_min']) {
				$result['guests_min'] = $item['guests_min'];
			}

			if ($item['guests_max'] > $result['guests_max']) {
				$result['guests_max'] = $item['guests_max'];
			}

			foreach($item['daysAvailable'] as $dayAvailable) {
				if (!in_array($dayAvailable, $result['daysAvailable'])) {
					$result['daysAvailable'][] = $dayAvailable;
				}
			}

			foreach($item['resultDate']['slots'] as $slot) {
				$result['resultDate']['slots'][] = $slot;
			}

		}

		return $result;
	}


	/**
	 * Create a new booking with status SELECTED
	 *
	 * @return void
	 */
	public function hold_booking()
	{
		//ray('hold booking');

		$restaurant_id = sanitize_text_field($_REQUEST['restaurantId']);

		// If backend then is configuring the widget
		$isBackend = sanitize_text_field($_REQUEST['isBackend']);
		if ($isBackend == 'yes') {
			wp_send_json_success([
				'uuid' => '12345678',
				'seconds' => 300
			]);
		}

		if ( ! $this->verify_nonce($restaurant_id) ) {
			wp_send_json_error(['error' => __eva('Cannot hold the booking. Invalid Nonce.')]);
		}

		$guests = intval($_REQUEST['guests']);
		$date = sanitize_text_field($_REQUEST['date']);
		$time = intval($_REQUEST['time']);
		$serviceId = intval($_REQUEST['serviceId']);
		$widgetId = intval($_REQUEST['widgetId']);

		// Check restaurant
		$restaurant = \Alexr\Models\Restaurant::where('active', 1)
		                                    ->where('id', $restaurant_id)
		                                    ->first();
		if (!$restaurant) {
			wp_send_json_error(['error' => __eva('Restaurant does not exists.')]);
		}

		// Check service exists
		$service = alexr_get_service($serviceId);
		if (!$service){
			wp_send_json_error(['error' => __eva('Service does not exists.')]);
		}
		$service_name = $service->name;

		// Check with closed days
		if ($restaurant->isDateClosed($date)){
			wp_send_json_error(['error' => __eva('Sorry, this date is closed for reservations.')]);
		}

		// Check with shifts closed
		if ($service->isSlotClosed($date, $time)){
			wp_send_json_error(['error' => __eva('Sorry, this slot is already closed.')]);
		}

		// Check availability again, return the tables available
		$isAvailable = $service->isAvailable($date, $time, $guests);
		if (!$isAvailable){
			wp_send_json_error(['error' => __eva('Sorry, this slot is not available any more.')]);
		}
		//ray($isAvailable);

		// Returned tables?
		$tables = null;
		if (is_array($isAvailable) && !empty($isAvailable)){
			$tables = $isAvailable;
		}
		// Comprobar mesas disponibles
		//ray($tables);


		$booking = \Alexr\Models\Booking::create([
			'restaurant_id' => $restaurant->id,
			'status' => \Alexr\Enums\BookingStatus::SELECTED,
			'date' => $date,
			'party' => $guests,
			'time' => $time,
			'widget_id' => $widgetId,
			'shift_event_id' => $serviceId,
			'shift_event_name' => $service_name,
			'first_name' => 'Unknown',
			'last_name' => 'Unknown',
			'phone' => '',
			'type' => \Alexr\Enums\BookingType::ONLINE
		]);


		$booking->save();

		if ($tables){
			$tables_id = array_map(function($table){
				return $table['id'];
			}, $tables);
			$booking->tables()->sync($tables_id);
			$booking->save();
		}


		$can_select_area = 'no';
		$max_covers_per_area = null;

		if (in_array($service->availability_type, ['tables', 'specific_tables']))
		{
			$can_select_area = $service->can_select_area;
		}
		else if (in_array($service->availability_type, ['volume_total']))
		{
			$can_select_area = $service->covers_can_select_area;
			if ($can_select_area == 'yes_area' || $can_select_area == 'yes_panorama_area')
			{
				$max_covers_per_area = $service->getMaxCoversPerArea($date, $time, $guests);
			}
		}

		wp_send_json_success([
			'uuid' => $booking->uuid,
			'seconds' => 300,
			'payment' => $this->gatewayPaymentDetails($service, $booking),

			'availability_type' => $service->availability_type,
			'can_select_area' => $can_select_area,
			'area_is_required' => $service->covers_area_is_required == 1 ? true : false,

			// For volume_total
			'max_covers_per_area' => $max_covers_per_area,

			'show_service_duration' => $service->showServiceDurationInWidget($widgetId)
		]);

	}

	/**
	 * Remove holded booking
	 *
	 * @return void
	 */
	public function hold_booking_remove()
	{
		$isBackend = sanitize_text_field($_REQUEST['isBackend']);

		if ($isBackend == 'yes') {
			wp_send_json_success();
		}

		$restaurant_id = intval($_REQUEST['restaurantId']);

		if ( ! $this->verify_nonce($restaurant_id) ) {
			wp_send_json_error(['error' => __eva('Forbidden to remove the booking.')]);
		}

		$booking_uuid = sanitize_text_field($_REQUEST['uuid']);

		if ($booking_uuid) {
			\Alexr\Models\Booking::where('uuid', $booking_uuid)
                 ->update(['status' => BookingStatus::DELETED]);
                 //->delete();
		}

		wp_send_json_success();
	}

	/**
	 * Get the fields for the booking form
	 *
	 * @return void
	 */
	public function get_fields()
	{
		$restaurant_id = sanitize_text_field($_REQUEST['restaurantId']);

		if ( ! $this->verify_nonce($restaurant_id) ) {
			wp_send_json_error(['error' => __eva('Could not get the fields.')]);
		}

		$widget_id = intval($_REQUEST['widgetId']);
		$service_id = intval($_REQUEST['serviceId']);

		$restaurant = \Alexr\Models\Restaurant::where('active', 1)
		                                    ->where('id', $restaurant_id)
		                                    ->first();

		if (!$restaurant) {
			wp_send_json_error(['error' => 'Restaurant not found.']);
		}

		$controller = new \Alexr\Http\Controllers\SearchBookingsController();
		$fields = $controller->fields(null, $restaurant->id, $widget_id, $service_id);

		wp_send_json_success($fields);
	}

	/**
	 * Get the list of Booking Tags
	 *
	 * @return void
	 */
	public function get_booking_tags()
	{
		$restaurant_id = sanitize_text_field($_REQUEST['restaurantId']);

		if ( ! $this->verify_nonce($restaurant_id) ) {
			wp_send_json_error(['error' => __eva('Forbidden to remove the booking.')]);
		}

		$restaurant = \Alexr\Models\Restaurant::where('active', 1)
		                                    ->where('id', $restaurant_id)
		                                    ->first();

		if (!$restaurant){
			wp_send_json_error(['error' => 'Restaurant not found.']);
		}

		$tags = BTag::where('restaurant_id', $restaurant_id)
		            ->orderBy('ordering', 'ASC')
		            ->get();

		$final_tags = [];
		foreach($tags as $tag) {
			$final_tags[] = $tag->toArray();
		}

		$groups = BTagGroup::where('restaurant_id', $restaurant->id)
		                   ->orderBy('ordering', 'ASC')
		                   ->get();

		$final_groups = [];
		foreach($groups as $group) {
			$final_groups[] = $group->toArray();
		}

		wp_send_json_success([
			'groups' => $final_groups,
			'tags' => $final_tags
		]);
	}

	/**
	 * Create the booking
	 *
	 * @return void
	 */
	function make_booking( $is_modify_booking = false ) {

		//ray('make booking: ' . $is_modify_booking);

		$restaurantId = intval($_REQUEST['restaurantId']);
		$serviceId = intval($_REQUEST['serviceId']);

		// Check restaurant exists
		$restaurant = \Alexr\Models\Restaurant::find($restaurantId);
		if (!$restaurant){
			wp_send_json_error([
				'message' => 'Wrong restaurant'
			]);
		}

		// Check service exists
		$service = alexr_get_service($serviceId);
		if (!$service) {
			wp_send_json_error(['message' => __eva('Service not found.')]);
		}

		// Check nonce
		if ( ! $this->verify_nonce($restaurantId) ) {
			wp_send_json_error(['message' => __eva('Could not create a booking. Invalid nonce. Please try again.')]);
		}


		$date = sanitize_text_field($_REQUEST['dateSelected']);
		$time = intval($_REQUEST['timeSelected']);
		$party = intval($_REQUEST['party']);

		// Create the booking on the fly
		// Could be a holded booking or if time expired is a new booking
		$uuid = sanitize_text_field($_REQUEST['uuid']);

		$booking = \Alexr\Models\Booking::where('uuid', $uuid)
		                                ->where('status', '!=', BookingStatus::DELETED)
		                                ->first();

		// Lo necesito si es una modificacion
		$old_attributes = [];

		// uuid corresponde a la nueva reserva modificada
		// modify_booking_uuid es la reserva que habia hecho anteriormente

		if ($is_modify_booking)
		{
			// No borrar la reserva actual hasta hacer las modificaciones
			$new_booking = $booking;

			// Esta es la reserva antogua
			$modify_booking_uuid = sanitize_text_field($_REQUEST['modify_booking_uuid']);
			//ray('NEW BOOKING ' . $uuid);
			//ray('OLD BOOKING ' .$modify_booking_uuid);

			// $modify_booking_uuid es la reserva original
			// Voy a modificar la antigua reserva con los nuevos datos para mantener en su sitio
			// las notificaciones, y luego pongo la nueva reserva como deleted
			$booking = \Alexr\Models\Booking::where('uuid', $modify_booking_uuid)
			                                ->where('status', '!=', BookingStatus::DELETED)
			                                ->first();
			if (!$booking){
				wp_send_json_error(['message' => __eva('Booking does not exist.')]);
			}

			$old_attributes = ListenBookingEvents::getOriginal($booking);

			// Check with closed days
			if ($restaurant->isDateClosed($date)){
				wp_send_json_error(['message' => __eva('Sorry, this date is closed for reservations.')]);
			}
			// Check with shifts closed
			if ($service->isSlotClosed($date, $time)){
				wp_send_json_error(['message' => __eva('Sorry, this slot is already closed.')]);
			}

			// Copia a la reserva nueva los datos de la reserva anterior para tener un registro de la modificacion
			// Y ponla como borrada
			if ($new_booking && $uuid != $modify_booking_uuid) {
				$new_booking->date = $booking->date;
				$new_booking->party = $booking->party;
				$new_booking->time = $booking->time;
				$new_booking->widget_id = $booking->widget_id;
				$new_booking->shift_event_id = $booking->shift_event_id;
				$new_booking->shift_event_name = $booking->shift_event_name;
				$new_booking->parent_booking_id = $booking->id;
				$new_booking->customer_id = $booking->customer_id;
				$new_booking->status = BookingStatus::DELETED;
				$new_booking->save();
			}


			$booking->date = $date;
			$booking->party = $party;
			$booking->time = $time;
			$booking->widget_id = intval($_REQUEST['widgetId']);
			$booking->shift_event_id = $serviceId;
			$booking->shift_event_name = $service->name;

			// Coger las mesas de la nueva reserva en modo hold
			$tablesList = $new_booking->tablesList;
			if (!empty($tablesList)) {
				$booking->tables()->sync($tablesList);
				$booking->save();
			}

			/*if ($new_booking && $uuid != $modify_booking_uuid) {
				$new_booking->status = BookingStatus::DELETED;
				$new_booking->save();
			}*/
		}

		// Holded booking to reserved
		else {
			// If holded booking does not exists because has been deleted
			// then need to create a new one ?
			if (!$booking) {

				wp_send_json_error(['message' => __eva('Time expired, please try again.')]);

				/*$booking = new \Alexr\Models\Booking([
					'restaurant_id' => $restaurantId,
					'date' => $date,
					'party' => $party,
					'time' => $time,
					'widget_id' => intval($_REQUEST['widgetId']),
					'shift_event_id' => $serviceId,
					'shift_event_name' => $service->name,
					'first_name' => 'Unknown',
					'last_name' => 'Unknown',
					'phone' => '',
					'type' => \Alexr\Enums\BookingType::ONLINE
				]);*/
			}
		}

		// For new booking
		if (!$is_modify_booking) {

			// Check email duplicates
			if ($this->isBookingDuplicated()) {
				wp_send_json_error(['message' => __eva('You cannot reserve again for this date.')]);
			}

			//ray('SIGUE EL PROCESO');

			// For now I'm going to set this status, can change later
			$booking->status = BookingStatus::PENDING;
			$this->updateBookingValuesFromRequest($booking);
			$this->assignOrCreateNewCustomerIfNeeded($booking);
		}


		// Status depends on the shift/event selected
		$status = $service->getBookingStatusForNewReservation($date, $time, $party, $booking->email);

		$booking->duration = $service->getDuration($booking->party);
		$booking->status = $status;

		// Save area_selected and table_selected and assign table if possible
		$this->attachAreaTableSelected($booking, $service, $date, $time, $party);

		// Status can also be pending depending on the tables selected
		$service->applyRulePendingForSpecificTables($booking);

		// Save
		//\Evavel\Query\Query::setDebug(true);
		$booking->save();
		//ray($booking);


		// RE-CHECK THE BOOKING HAS BEEN STORED CORRECTLY
		$new_booking_saved = \Alexr\Models\Booking::find($booking->id);
		if (!$new_booking_saved) {
			//ray('No existe new booking');
			// Send error message to the restaurant and save it in the log
			$data = json_encode($booking->toArray());
			evavel_log_to_file(\Evavel\Log\Log::AJAX_ERROR_SAVE_BOKING, $data);
			wp_send_json_error(['message' => __eva('Sorry, something went wrong, please try again.')]);
		}
		//\Evavel\Query\Query::setDebug(false);



		// ++++++++++++ PAYMENT ++++++++++++++++++++++++++++++++++++++
		// Check if payment is needed - SEND NOTIFICATIONS
		// +++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
		$isPaymentNeeded = $this->isPaymentNeeded($service, $booking);

		// Modify booking does not need to pay again ++++++++++++++++++++++++++++
		if (!$is_modify_booking && $isPaymentNeeded)
		{
			$booking->status = BookingStatus::PENDING_PAYMENT;
			$booking->save();

			$payment = $this->gatewayPaymentDetails($service, $booking);
			$message = $service->getPaymentMessage();
		}

		//  Payment not needed
		else
		{
			// Send email and return message - language adapted
			$message = $this->sendBookingNotifications($booking, false, false, $is_modify_booking);
			$payment = null;
		}

		if (!$is_modify_booking) {
			evavel_event(new \Alexr\Events\EventBookingCreated($booking));
		} else {
			evavel_event(new \Alexr\Events\EventBookingModified($booking, null, $old_attributes));
		}


		wp_send_json_success([
			'success' => true,
			'message' => $message,
			'payment' => $payment,
			'booking' => [
				'id' => $booking->id,
				'status' => $booking->status,
				'uuid' => $booking->uuid,
				'email' => $booking->email,
				'shift' => $booking->shiftName,
				'date' => $booking->date,
				'time' => $booking->time,
				'guests' => $booking->party,
				'duration' => $booking->duration,
				'language' => $booking->language,
				'first_name' => $booking->first_name,
				'last_name' => $booking->last_name,
				'calendar_links' => $booking->getCalendarLinks()
			],
		]);
	}

	function attachAreaTableSelected($booking, $service, $date, $time, $party)
	{
		$area_selected_id = intval($_REQUEST['area_selected_id']);
		$table_selected_id = intval($_REQUEST['table_selected_id']);

		if ($area_selected_id > 0) {
			$booking->area_selected_id = $area_selected_id;
		} else {
			$booking->area_selected_id = null;
		}

		// Table selected is used only with availability_type tables
		if ($table_selected_id > 0)
		{
			$booking->table_selected_id = $table_selected_id;

			// Attach table to the booking
			if ($service->isTableFreeForGuests($date, $time, $party, $table_selected_id)){
				$booking->tables()->sync([$table_selected_id]);
			}

		} else {
			$booking->table_selected_id = null;
		}

		// Buscar una mesa o grupo que este en ese area seleccionada
		if ( in_array($service->availability_type, ['tables', 'specific_tables'])
		     && $booking->area_selected_id != null && $booking->table_selected_id == null)
		{
			$singleTablesRequireToBeOnlineForGroup = false;
			$tables_assigned = $service->isAvailableBy_SearchTables($date, $time, $party, $singleTablesRequireToBeOnlineForGroup, $booking->area_selected_id);

			if (!empty($tables_assigned))
			{
				$list_ids = [];
				foreach($tables_assigned as $table) {
					$list_ids[] = $table['id'];
				}
				$booking->tables()->sync($list_ids);
			}
		}
	}

	function sendBookingNotifications($booking, $lang = false, $skip_admin_notifications = false, $is_modify_booking = false)
	{
		if (!$lang){
			$lang = isset($_REQUEST['lang']) ? sanitize_text_field($_REQUEST['lang']) : 'en';
		}

		if ($booking->status == BookingStatus::PENDING)
		{
			// Envia dos emails, el de la modification y el de pending
			if ($is_modify_booking) {
				$booking->sendEmailModified($lang);
			}
			$booking->sendEmailPending($lang);

			if (!$skip_admin_notifications)
			{
				if ($is_modify_booking)
				{
					$booking->sendEmailAdminModified();
				} else
				{
					$booking->sendEmailAdminPending();
				}
			}

			// Widget message pending
			$message = $booking->messagePending($lang);

			if ($booking->agree_receive_sms == 1) {
				$booking->sendSmsPending($lang);
			}
		}
		else if ($booking->status == BookingStatus::BOOKED)
		{
			// Envia el de la modification y el de la confirmacion
			if ($is_modify_booking) {
				$booking->sendEmailModified($lang);
			}
			$booking->sendEmailBooked($lang);

			if (!$skip_admin_notifications)
			{
				if ($is_modify_booking)
				{
					$booking->sendEmailAdminModified();
				} else
				{
					$booking->sendEmailAdminBooked();
				}

			}

			// Widget message booked
			$message = $booking->messageBooked($lang);

			if ($booking->agree_receive_sms == 1) {
				$booking->sendSmsBooked($lang);
			}
		}

		// This is used only when the admin change the status from the front view
		// so the message returned is not displayed anywhere
		else if ($booking->status == BookingStatus::DENIED)
		{
			$booking->sendEmailDenied($lang);
			$message = '';
			if ($booking->agree_receive_sms == 1) {
				$booking->sendSmsDenied($lang);
			}
		}


		// Dashboard notification
		if ($booking->restaurant) {
			$booking->restaurant->notify( new \Alexr\Notifications\BookingOnlineReceived( $booking ) );
		}

		return $message;
	}

	function isBookingDuplicated()
	{
		// Check widget exists
		$widget_id = intval($_REQUEST['widgetId']);
		$serviceId = intval($_REQUEST['serviceId']);

		$w_form = \Alexr\Settings\WidgetForm::where('id', $widget_id)->first();
		if (!$w_form) return false;

		// Check if option is enabled
		$prevent_duplicate_bookings = $w_form->form_config['prevent_duplicate_bookings'];
		if ($prevent_duplicate_bookings == 'no') return false;

		$email = sanitize_text_field($_REQUEST['email']);
		$widget_id = intval($_REQUEST['widgetId']);
		$date = sanitize_text_field($_REQUEST['dateSelected']);

		// Find any booking that belongs to this widget with same email and date and shift
		// permite diferentes formularios
		$booking = \Alexr\Models\Booking::where('email', $email)
		                                ->where('date', $date)
										//->where('widget_id', $widget_id)
										->where('shift_event_id', $serviceId)
		                                ->where('status', '!=', BookingStatus::CANCELLED)
										->where('status', '!=', BookingStatus::DELETED)
										->where('status', '!=', BookingStatus::DENIED)
		                                ->first();

		if ($booking) {
			return true;
		}

		return false;
	}

	/**
	 * Cancel a booking
	 * Can be called from 2 places:
	 * 1. From the last step of the Widget form where the user can decide to cancel (mode == widget-form)
	 * 2. From the view booking page that can be accessed from the email link sent to the user (mode == view-booking)
	 *
	 * @return void
	 */
	function cancel_booking()
	{
		//ray('USER CANCELLED BOOKING');

		$restaurant_id = intval($_REQUEST['restaurantId']);

		$booking_uuid = sanitize_text_field($_REQUEST['uuid']);

		if (!$booking_uuid) {
			wp_send_json_error(['error' => __eva('Booking not found. Cannot be cancelled.')]);
		}

		// Verify nonce depends on the mode
		$mode = sanitize_text_field($_REQUEST['mode']);
		if (!$mode){
			wp_send_json_error(['error' => __eva('Mode not defined.')]);
		}

		if ($mode == 'widget-form'){

			if ( ! $this->verify_nonce($restaurant_id) ) {
				wp_send_json_error(['error' => __eva('Cannot cancel the booking.')]);
			}

		} else if ($mode == 'view-booking') {

			$nonce = sanitize_text_field($_REQUEST['nonce']);

			if (!evavel_verify_nonce($nonce, 'booking-'.$booking_uuid)) {
				wp_send_json_error(['error' => __eva('Invalid booking.')]);
			}

		} else {
			wp_send_json_error(['error' => __eva('Mode is not valid.')]);
		}


		// Check booking exists
		$booking = \Alexr\Models\Booking::where('uuid', $booking_uuid)->where('status', '!=', BookingStatus::DELETED)
		                                                              ->first();

		if (!$booking){
			wp_send_json_error(['error' => __eva('Booking not found. Cannot be cancelled.')]);
		}

		$old_status = $booking->status;
		$booking->status = BookingStatus::CANCELLED;
		$booking->save();

		evavel_event(new \Alexr\Events\EventBookingStatusChangedByCustomer($booking, $old_status, BookingStatus::CANCELLED));

		$lang = 'en';
		if (isset($_REQUEST['lang'])){
			$lang = sanitize_text_field($_REQUEST['lang']);
		}

		// Send email
		$booking->sendEmailCancelled($lang);
		$booking->sendEmailAdminCancelled();

		// Notificar al cliente
		$booking->restaurant->notify(new \Alexr\Notifications\BookingCancelledByUser($booking));

		wp_send_json_success();
	}

	function confirm_booking()
	{
		$restaurant_id = intval($_REQUEST['restaurantId']);

		$booking_uuid = sanitize_text_field($_REQUEST['uuid']);

		if (!$booking_uuid) {
			wp_send_json_error(['error' => __eva('Booking not found. Cannot be confirmed.')]);
		}

		// Verify nonce depends on the mode
		$mode = sanitize_text_field($_REQUEST['mode']);
		if (!$mode){
			wp_send_json_error(['error' => __eva('Mode not defined.')]);
		}

		if ($mode == 'widget-form'){

			if ( ! $this->verify_nonce($restaurant_id) ) {
				wp_send_json_error(['error' => __eva('Cannot confirm the booking.')]);
			}

		} else if ($mode == 'view-booking') {

			$nonce = sanitize_text_field($_REQUEST['nonce']);

			if (!evavel_verify_nonce($nonce, 'booking-'.$booking_uuid)) {
				wp_send_json_error(['error' => __eva('Invalid booking.')]);
			}

		} else {
			wp_send_json_error(['error' => __eva('Mode is not valid.')]);
		}


		// Check booking exists
		$booking = \Alexr\Models\Booking::where('uuid', $booking_uuid)
                    ->where('status', BookingStatus::BOOKED)
                    ->first();

		if (!$booking){
			wp_send_json_error(['error' => __eva('Booking not found. Cannot be confirmed.')]);
		}

		$old_status = $booking->status;
		$booking->status = BookingStatus::CONFIRMED;
		$booking->save();

		evavel_event(new \Alexr\Events\EventBookingStatusChangedByCustomer($booking, $old_status, BookingStatus::CONFIRMED));

		$lang = 'en';
		if (isset($_REQUEST['lang'])){
			$lang = sanitize_text_field($_REQUEST['lang']);
		}

		$booking->sendEmailConfirmed($lang);
		$booking->sendEmailAdminConfirmed();

		$booking->restaurant->notify(new \Alexr\Notifications\BookingConfirmedByUser($booking));

		wp_send_json_success();
	}

	function get_booking()
	{
		$restaurant_id = sanitize_text_field($_REQUEST['restaurantId']);
		$nonce = sanitize_text_field($_REQUEST['nonce']);
		$uuid = sanitize_text_field($_REQUEST['uuid']);

		if (!evavel_verify_nonce($nonce, 'booking-'.$uuid)) {
			wp_send_json_error(['error' => __eva('Invalid booking.')]);
		}

		$booking = \Alexr\Models\Booking::where('uuid', $uuid)->where('status', '!=', BookingStatus::DELETED)->first();

		if (!$booking) {
			wp_send_json_error(['error' => __eva('Invalid booking.')]);
		}

		wp_send_json_success([
			'booking' => \Alexr\Models\Booking::toDataArray($booking),
		]);
	}

	function modify_booking()
	{
		return $this->make_booking(true);
	}

	/**
	 * Update booking data
	 *
	 * @param $booking
	 *
	 * @return void
	 */
	protected function updateBookingValuesFromRequest($booking)
	{
		//ray($_REQUEST);

		$first_name = isset($_REQUEST['first_name']) ? sanitize_text_field($_REQUEST['first_name']) : 'Guest';
		$last_name = isset($_REQUEST['last_name']) ? sanitize_text_field($_REQUEST['last_name']) : '';

		$booking->first_name = $first_name;
		$booking->last_name = $last_name;
		$booking->email = sanitize_text_field($_REQUEST['email']);

		// First save point
		$booking->save();


		if (isset($_REQUEST['source'])) {
			$source = sanitize_text_field($_REQUEST['source']);
			if (!empty($source)) {
				$booking->source = $source;
			}
		}

		if (isset($_REQUEST['phone'])) {
			$phone = sanitize_text_field($_REQUEST['phone']);
			if (!empty($phone)) {
				$booking->phone = $phone;
			}
		}

		if (isset($_REQUEST['dial_code_country'])) {
			$dial_code_country = sanitize_text_field($_REQUEST['dial_code_country']);
			if (!empty($dial_code_country)) {
				$booking->dial_code_country = $dial_code_country;
			}
		}

		if (isset($_REQUEST['dial_code'])) {
			$dial_code = sanitize_text_field($_REQUEST['dial_code']);
			if (!empty($dial_code)) {
				$booking->dial_code = $dial_code;
			}
		}

		if (isset($_REQUEST['country_code'])) {
			$country_code = sanitize_text_field($_REQUEST['country_code']);
			if (!empty($country_code)) {
				$booking->country_code = $country_code;
			}
		}

		if (isset($_REQUEST['birthday'])) {
			$birthday = sanitize_text_field($_REQUEST['birthday']);
			if (!empty($birthday)) {
				$booking->birthday = $birthday;
			}
		}

		if (isset($_REQUEST['notes'])) {
			$notes = sanitize_text_field($_REQUEST['notes']);
			if (!empty($notes)) {
				$booking->notes = $notes;
			}
		}


		// Language
		$booking->language = 'en';
		if (isset($_REQUEST['lang'])){
			$lang = sanitize_text_field($_REQUEST['lang']);
			$booking->language = $lang;
		}

		// Gateway
		if (isset($_REQUEST['gateway'])){
			$gateway = sanitize_text_field($_REQUEST['gateway']);
			$booking->gateway = $gateway;
		}

		// Receive email always true
		$booking->agree_receive_email = 1;

		// Receive sms has to be enabled
		$booking->agree_receive_sms = 1;

		if (isset($_REQUEST['agree_sms'])) {
			$agree_sms = sanitize_text_field($_REQUEST['agree_sms']);
			if ($agree_sms == 'true') {
				$booking->agree_receive_sms = 1;
			} else {
				$booking->agree_receive_sms = 0;
			}
		}

		// Second save point
		$booking->save();


		// Tags
		if (isset($_REQUEST['tags'])) {
			$tags = sanitize_text_field($_REQUEST['tags']);
			$tags = str_replace(['[',']'], '', $tags);

			if ($tags != null && $tags != 'null' && $tags != '') {
				$tags = explode(',', $tags);
				if (!empty($tags)) {
					$booking->tags()->sync($tags);
				}
			}
		}


		// Custom fields
		// Save the field component so can be changed later if needed
		$widget_id = $booking->widget_id;
		$restaurant_id = $booking->restaurant_id;

		$controller = new \Alexr\Http\Controllers\SearchBookingsController();
		$fields = $controller->fields(null, $restaurant_id, $widget_id, $booking->shift_event_id);

		$custom_fields = [];

		foreach($_REQUEST as $key => $value)
		{
			if (preg_match('#custom_#', $key))
			{
				$the_field = null;
				foreach ( $fields as $field ) {
					if ($field['attribute'] == $key) {
						$the_field = $field;
					}
				}

				// Transform checkbox
				if ($the_field['type'] == 'checkbox'){
					$value = $value == 'true' ? true : false;
				}
				// Options
				else if ($the_field['type'] == 'options'){
					$value = str_replace('\"', '"', $value);
					$value = json_decode($value, true);
				}
				else if ($the_field['type'] == 'select'){
					$value = str_replace('\"', '"', $value);
					$value = json_decode($value, true);
				}

				// Save
				$custom_fields[$key] = [
					'field' => $the_field,
					'value' => $value
				];
			}

			$booking->custom_fields = $custom_fields;
		}


		// Voy a guardar por si acaso
		evavel_log_to_file(\Evavel\Log\Log::AJAX_SAVE_BOOKING_VALUES, json_encode($booking->toArray()));
		$booking->save();
	}

	/**
	 * Assign Booking user (search existing or create a new one)
	 *
	 * @param $booking
	 *
	 * @return void
	 */
	protected function assignOrCreateNewCustomerIfNeeded($booking)
	{
		if ($booking->customer_id != null) return;

		$customer_found = \Alexr\Models\Customer::where('email', $booking->email)->first();

		if ($customer_found) {
			$booking->customer_id = $customer_found->id;
			$customer_found->calculateVisits();
			$this->updateCustomerConditions($customer_found);
			return;
		}

		$customer = alexr_create_new_customer($booking);
		if ($customer){
			$booking->customer_id = $customer->id;
			$customer->calculateVisits();
			$this->updateCustomerConditions($customer);
		}

	}

	/**
	 * Update Flags for receiving email marketing and SMS
	 *
	 * @param $customer
	 *
	 * @return void
	 */
	protected function updateCustomerConditions($customer)
	{
		if (isset($_REQUEST['agree_email_news'])) {
			$agree_email_news = sanitize_text_field($_REQUEST['agree_email_news']);
			if ($agree_email_news == 'true') {
				$customer->agree_receive_email_marketing = 1;
				$customer->agree_receive_email = 1;
			} else {
				$customer->agree_receive_email_marketing = 0;
			}
		}

		if (isset($_REQUEST['agree_sms'])) {
			$agree_sms = sanitize_text_field($_REQUEST['agree_sms']);
			if ($agree_sms == 'true') {
				$customer->agree_receive_sms = 1;
			} else {
				$customer->agree_receive_sms = 0;
			}
		}

		$customer->save();
	}


	/**
	 * Admin can change status at the front-end without using the dashboard
	 *
	 * @return void
	 */
	public function update_booking_status()
	{
		//ray('UPDATE BOOKING STATUS');
		$restaurantId = intval($_REQUEST['restaurantId']);

		// Check restaurant exists
		$restaurant = \Alexr\Models\Restaurant::find($restaurantId);
		if (!$restaurant){
			wp_send_json_error([
				'message' => 'Wrong restaurant'
			]);
		}

		$nonce = sanitize_text_field($_REQUEST['nonce']);
		$uuid = sanitize_text_field($_REQUEST['uuid']);
		$new_status = sanitize_text_field($_REQUEST['status']);

		// Check nonce
		if (!evavel_verify_nonce($nonce, 'booking-'.$uuid)) {
			wp_send_json_error(['message' => __eva('Invalid nonce booking.')]);
		}

		$booking = \Alexr\Models\Booking::where('uuid', $uuid)->first();

		if (!$booking) {
			wp_send_json_error(['message' => __eva('Booking does not exist.')]);
		}

		// Check correct status
		$list = BookingStatus::listing();
		if (!in_array($new_status, array_keys($list))){
			wp_send_json_error(['message' => __eva('Wrong status.')]);
		}


		$email_actions = $booking->getMeta('email_actions');

		if ($email_actions == null){
			$booking->setMeta('email_actions', 1);
		} else {
			wp_send_json_error(['message' => __eva('Cannot change status again.')]);
			//$booking->setMeta('email_actions', 1 + intval($email_actions));
		}

		$old_status = $booking->status;
		$booking->status = $new_status;
		$booking->save();


		evavel_event(new \Alexr\Events\EventBookingStatusChanged($booking, $old_status, $new_status, null, true));


		$this->sendBookingNotifications($booking, $booking->language, true);

		wp_send_json_success(['success' => true]);
	}

}


new ALEXR_Ajax_Actions;
