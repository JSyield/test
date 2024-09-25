<?php

namespace Alexr\Settings;

use Alexr\Models\Restaurant;

// This should be managed like a model but using a
// special type of table with meta_key, meta_value
class Event extends Scheduler
{
	public static $meta_key = 'event';

	protected $casts = [
		'active' => 'boolean'
	];

	public static function description() {
		return __eva('Events can overwrite Shifts and are used for specific days only.');
	}

	public function restaurant()
	{
		return $this->belongsTo(Restaurant::class);
	}

	public function defaultValue()
	{
		$values = [
			'active' => true,
			'type' => 'event',
			'name' => 'New Event',
			'start_date' => evavel_date_now()->addDays(1)->format('Y-m-d'),
			'end_date' => evavel_date_now()->addDays(1)->format('Y-m-d'),
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

			'booking_status' => 'pending',
			'status_confirmed_covers' => 50,

			'duration_mode' => 'time',
			'duration_time' => 5400,
			'duration_covers' => [
				['label' => 1, 'value' => 3600],
				['label' => 2, 'value' => 5400],
				['label' => 3, 'value' => 7200],
				['label' => 4, 'value' => 10800],
			],
			'notes' => ''
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

		// Current event
		$cu_dates = $this->include_dates;
		// Could be receiving '[]'
		if (!is_array($cu_dates)){
			$cu_dates = [];
		}
		$cu_first_seating = intval($this->first_seating);
		$cu_last_seating = intval($this->last_seating);

		foreach($shifts as $shift) {

			// Check days coincidence
			$is_overlapping_date = false;
			if (is_array($shift->include_dates)){
				foreach($shift->include_dates as $the_date){
					if (in_array($the_date, $cu_dates)){
						$is_overlapping_date = $the_date;
						continue;
					}
				}
			}

			if (!$is_overlapping_date) continue;

			// Is overlapping some date so I have to check hour seating
			$first_seating = intval($shift->first_seating);
			$last_seating = intval($shift->last_seating);

			$has_same_time = false;
			if ($cu_first_seating >= $first_seating && $cu_first_seating >= $last_seating){
				$has_same_time = true;
			} else if ($cu_last_seating >= $first_seating && $cu_last_seating <= $last_seating) {
				$has_same_time = true;
			}
			if (!$has_same_time) continue;

			$list_errors[] = __eva('Overlapping with event').' '.$shift->name.__eva(' on date ').$is_overlapping_date;
		}

		return $list_errors;
	}
}
