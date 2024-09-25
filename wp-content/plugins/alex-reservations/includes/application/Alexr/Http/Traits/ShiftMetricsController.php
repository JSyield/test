<?php

namespace Alexr\Http\Traits;

use Alexr\Models\Restaurant;
use Evavel\Http\Request\Request;

trait ShiftMetricsController {

	public function shiftMetrics(Request $request)
	{
		$restaurant_id = $request->tenant;
		$shift_id = $request->shiftId; // 0 means ALL shifts and events for this day
		$date = $request->date;


		$restaurant = Restaurant::find($restaurant_id);
		if (!$restaurant){
			return $this->response(['success' => false, 'error' => __('Restaurant is not correct')]);
		}

		$active = $restaurant->isOnlineBookingsEnabled($shift_id, $date);

		return $this->response([
			'success' => true,
			'metrics' => [
				'active' => $active,
				'seatings_occupied' => $restaurant->getSeatsOccupied($shift_id, $date),
                'seatings_total' => $restaurant->getSeatsTotal($shift_id, $date),
                'tables_occupied' => $restaurant->getTablesOccupied($shift_id, $date),
                'tables_total' => $restaurant->getTablesTotal($shift_id, $date)
			]
		]);
	}

	/**
	 * Enable / Disable online bookings for a shift and date
	 * @param Request $request
	 *
	 * @return \WP_REST_Response
	 */
	public function updateShiftMetrics(Request $request)
	{
		$restaurant_id = $request->tenant;
		$shift_id = $request->shiftId; // 0 means ALL shifts and events for this day
		$date = $request->date;
		$active = $request->active;

		$restaurant = Restaurant::find($restaurant_id);
		if (!$restaurant){
			return $this->response(['success' => false, 'error' => __('Restaurant is not correct')]);
		}

		if ($restaurant->isOnlineBookingsEnabled($shift_id, $date)){
			$restaurant->disableOnlineBookings($shift_id, $date);
		} else {
			$restaurant->enableOnlineBookings($shift_id, $date);
		}

		return $this->response(['success' => true, 'metrics' => [
			'active' => $restaurant->isOnlineBookingsEnabled($shift_id, $date)
		]]);
	}
}
