<?php

namespace Alexr\Settings;

use Alexr\Enums\BookingStatus;
use Alexr\Models\Restaurant;

// This should be managed like a model but using a
// special type of table with meta_key, meta_value
class Shift extends Scheduler
{
	public static $meta_key = 'shift';

	protected $casts = [
		'active' => 'boolean',
		'covers_areas_show_image' => 'boolean',
		'covers_areas_show_free_seats' => 'boolean'
	];

	public static function description() {
		return '';
		return __eva('Shifts are recurring.'); //.'<br>'.
		return __eva('Shifts are recurring and should not overlap.'); //.'<br>'.
		       //__eva('To overwrite shifts for special days use Events.');
		       //__eva('To block specific days/hours use Block Hours.');
	}

	public function restaurant()
	{
		return $this->belongsTo(Restaurant::class);
	}

	public function defaultValue()
	{
		$values = [
			'active' => true,
			'type' => 'recurring',
			'name' => 'New shift',
			'start_date' => evavel_date_now()->addDays(-1)->format('Y-m-d'),
			'end_date' => evavel_date_now()->addDays(365)->format('Y-m-d'),
			'include_dates' => [],
			'exclude_dates' => [],
			'days_of_week' => [
				'sun' => false,
				'mon' => false,
				'tue' => false,
				'wed' => false,
				'thu' => false,
				'fri' => false,
				'sat' => false
			],
			'first_seating' => 43200,
			'last_seating' => 46800,
			'min_covers_reservation' => 2,
			'max_covers_reservation' => 8,
			'color' => '#ff9922',
			'color_text' => '#ffffff',
			'interval' => 1800,
			'block_slots' => [],
			'availability_type' => 'volume_total',
			'availability_total' => 100,
			'availability_slots' => [
				['time' => 43200, 'covers' => 100],
				['time' => 45000, 'covers' => 0],
				['time' => 46800, 'covers' => 0],
				['time' => 48600, 'covers' => 0],
				['time' => 50400, 'covers' => 0],
				['time' => 52200, 'covers' => 0],
				['time' => 54000, 'covers' => 0],
			],

			'close_reservation_mode' => 'until_last_minute',
			'until_hours_period' => 3600,
			'until_same_day_time' => 43200,
			'until_previous_day_count' => 1,
			'until_previous_day_time' => 72000,

			'open_reservation_mode' => 'open_all_time',
			'open_hours_before' => 3600,
			'open_same_day_time' => 28800,
			'open_several_days_count' => 1,
			'open_several_days_time' => 28800,

			'booking_status' => 'confirmed',
			'status_confirmed_covers' => 50,

			'duration_mode' => 'time',
			'duration_time' => 5400,
			'duration_covers' => [
				['label' => 1, 'value' => 3600],
				['label' => 2, 'value' => 5400],
				['label' => 3, 'value' => 7200],
				['label' => 4, 'value' => 10800],
			],
			'notes' => '',

			'working_hours_mon' => false,
			'first_seating_mon' => 43200,
			'last_seating_mon' => 46800,
			'block_slots_mon' => false,

			'working_hours_tue' => false,
			'first_seating_tue' => 43200,
			'last_seating_tue' => 46800,
			'block_slots_tue' => false,

			'working_hours_wed' => false,
			'first_seating_wed' => 43200,
			'last_seating_wed' => 46800,
			'block_slots_wed' => false,

			'working_hours_thu' => false,
			'first_seating_thu' => 43200,
			'last_seating_thu' => 46800,
			'block_slots_thu' => false,

			'working_hours_fri' => false,
			'first_seating_fri' => 43200,
			'last_seating_fri' => 46800,
			'block_slots_fri' => false,

			'working_hours_sat' => false,
			'first_seating_sat' => 43200,
			'last_seating_sat' => 46800,
			'block_slots_sat' => false,

			'working_hours_sun' => false,
			'first_seating_sun' => 43200,
			'last_seating_sun' => 46800,
			'block_slots_sun' => false,
		];

		$values = apply_filters('alexr-shift-default-values', $values, $this);

		return $values;
	}

	public function validate() {

		$class = self::class;

		$shifts = $class::where('restaurant_id', $this->restaurant_id)
		                ->whereNotIn('id', [$this->id])
		                ->get()
		                ->toArray();

		$list_errors = [];

		// Current shift
		$cu_start_date = evavel_new_date($this->start_date);
		$cu_end_date = evavel_new_date($this->end_date);
		$cu_days_of_week = $this->days_of_week;
		$cu_first_seating = intval($this->first_seating);
		$cu_last_seating = intval($this->last_seating);

		foreach($shifts as $shift) {

			// Shift to compare
			$start_date = evavel_new_date($shift->start_date);
			$end_date = evavel_new_date($shift->end_date.' 23:59:59');
			$days_of_week = $shift->days_of_week;
			$first_seating = intval($shift->first_seating);
			$last_seating = intval($shift->last_seating);

			// Check hour seating
			$has_same_time = false;
			if ($cu_first_seating > $first_seating && $cu_first_seating < $last_seating){
				$has_same_time = true;
			} else if ($cu_last_seating > $first_seating && $cu_last_seating < $last_seating) {
				$has_same_time = true;
			}
			if (!$has_same_time) continue;

			// Check dates
			//ray($cu_start_date.' - '.$cu_end_date);
			$has_same_dates = false;
			if ($cu_start_date >= $start_date && $cu_start_date <= $end_date){
				$has_same_dates = true;
			} else if ($cu_end_date >= $start_date && $cu_end_date <= $end_date) {
				$has_same_dates = true;
			}
			if (!$has_same_dates) continue;

			// Check weekdays
			$has_same_weekdays = false;
			$weekdays = ['mon','tue','wed','thu','fri','sat','sun'];
			foreach($weekdays as $key){
				if ($cu_days_of_week[$key] === true && $days_of_week[$key] === true){
					$has_same_weekdays = true;
				}
			}
			if (!$has_same_weekdays) continue;

			$list_errors[] = __eva('Overlapping with shift').' '.$shift->name;
		}


		return $list_errors;
	}

	// No usar este, vale para cuando obtengo el listado completo de los items
	public static function validateAll() {
		return [];
	}
}
