<?php

use Alexr\Models\Area;
use Alexr\Models\Combination;
use Alexr\Models\Floor;
use Alexr\Models\Table;

trait Alexr_ajax_floorplan_actions
{
	public function get_floorplan_with_panoramas()
	{
		$response = $this->get_floorplan(true);

		if (isset($response['error'])) {
			wp_send_json_error($response);
		}

		$panorama = \Alexr\Settings\Panorama::where('restaurant_id', $response['restaurantId'])->first();
		$response['panoramas'] = $panorama->panoramas;

		wp_send_json_success($response);
	}

	public function get_floorplan($return_array = false)
	{
		$restaurantId = intval($_REQUEST['restaurantId']);
		$widgetId = intval($_REQUEST['widgetId']);
		$serviceId = intval($_REQUEST['serviceId']);
		$guests = intval($_REQUEST['guests']);
		$date_string = sanitize_text_field($_REQUEST['date']);
		$time = intval($_REQUEST['time']);
		$holdBooking_uuid = sanitize_text_field($_REQUEST['holdBooking_uuid']);

		// Check restaurant exists
		$restaurant = \Alexr\Models\Restaurant::find($restaurantId);
		if (!$restaurant){
			if ($return_array){
				return [ 'error' => __eva('Wrong restaurant') ];
			}
			wp_send_json_error([ 'error' => __eva('Wrong restaurant') ]);
		}

		// Check nonce
		//$nonce = sanitize_text_field($_REQUEST['nonce']);
		//if (!evavel_verify_nonce($nonce, 'booking-'.$uuid)) {
		//	wp_send_json_error(['error' => __eva('Invalid booking.')]);
		//}


		// Calculate free tables
		// Could be the booking has already a table automatically attached
		// Take into account the availability mode
		$service = alexr_get_service($serviceId);
		if (!$service){
			if ($return_array){
				return [ 'error' => __eva('Service does not exists.')];
			}
			wp_send_json_error(['error' => __eva('Service does not exists.')]);
		}
		// true -> force get tables for any type of service availability_type
		$tables = $service->getListTablesFree($date_string,$time,$guests,true);
		$tables = $service->restrictTablesForGuests($tables, $guests);
		$freeTables = array_map(function($table){ return $table['id']; }, $tables);
		$showAreaImages = $service->covers_areas_show_image;
		$showAreaFreeSeats = $service->covers_areas_show_free_seats;

		// Get areas
		//$areas = Area::where('restaurant_id', $restaurantId)->where('bookable_online',1)->get();
		$areas = Area::where('restaurant_id', $restaurantId)->get();
		$list_areas = [];
		foreach($areas as $area) {
			$area = $area->toArray();
			if (!$area['image_url'] || $area['image_url'] == 'null') {
				$area['image_url'] = ALEXR_PLUGIN_URL . 'assets/img/interior.webp';
			}
			$list_areas[] = $area;
		}

		// Get all tables
		$tables = Table::where('restaurant_id', $restaurantId)->get();
		$list_tables = [];
		foreach($tables as $table){
			$list_tables[] = $table->toArray();
		}

		// Get current table linked to this booking
		$bookingTables = [];
		$booking = \Alexr\Models\Booking::where('uuid', $holdBooking_uuid)->first();
		if ($booking){
			$tables = $booking->tables;
			if ($tables) {
				$bookingTables = $tables->map(function($item){
					return intval($item->id);
				})->toArray();
			}
		}

		// Include booking tables in the freetables list so the customer can select the tables already assigned
		if (is_array($freeTables) && is_array($bookingTables)) {
			$freeTables = array_merge($freeTables, $bookingTables);
		}

		// IF specific tables mode then I have to filter free tables to only those selected
		if ($service->availability_type == 'specific_tables') {
			$specific_tables = $service->list_of_tables;
			if (is_array($specific_tables) && count($specific_tables) > 0) {
				// Only free tables that are in this list are allows
				$newFreeTables = [];
				foreach($freeTables as $table_id) {
					if (in_array($table_id, $specific_tables)){
						$newFreeTables[] = $table_id;
					}
				}
				$freeTables = $newFreeTables;
			}
		}

		$response = [
			'restaurantId' => $restaurantId,
			'areas' => $list_areas,
			'tables' => $list_tables,
			'freeTables' => $freeTables,
			'bookingTables' => $bookingTables,
			'configAreas' => [
				'showAreaImages' => $showAreaImages,
				'showAreaFreeSeats' => $showAreaFreeSeats,
			]
			//'floors'    => Floor::where('restaurant_id', $restaurantId)->get()->toArray(),
			//'areas'     => Area::where('restaurant_id', $restaurantId)->get()->toArray(),
			//'tables'    => Table::where('restaurant_id', $restaurantId)->get()->toArray(),
			//'combinations'    => Combination::where('restaurant_id', $restaurantId)->get()->toArray(),
		];

		if ($return_array) {
			return $response;
		}

		wp_send_json_success($response);
	}

}
