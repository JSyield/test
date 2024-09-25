<?php

namespace Alexr\Settings\Traits;

use Alexr\Enums\BookingStatus;
use Alexr\Models\Area;
use Alexr\Models\Booking;
use Alexr\Models\Customer;
use Alexr\Models\Restaurant;
use Alexr\Models\Table;
use Alexr\Settings\ClosedDay;
use Alexr\Settings\ClosedSlot;
use Alexr\Settings\Event;
//use Alexr\Settings\Filters\FilterByBlockSlots;
use Alexr\Settings\Filters\FilterByClosedSlots;
use Alexr\Settings\Filters\FilterByDuration;
use Alexr\Settings\Filters\FilterByMinMaxGuests;
use Alexr\Settings\Filters\FilterByNearestSlots;
use Alexr\Settings\Shift;
use Evavel\Query\Query;

//use Carbon\Carbon;

trait ShiftCalculations {

	public $stepQuarterOfMinute = 900;

	/** TESTED
	 * Get time zone from the Restaurant associated
	 * @return string
	 */
	public function getTimezone() {

		$restaurant = Restaurant::where('id', $this->restaurant_id)->first();
		if ($restaurant) {
			return $restaurant->timezone;
		}

		return "UTC";
	}

	/** TESTED
	 * Check if some date is bookable
	 *
	 * @param $timezone
	 *
	 * @return bool
	 */
	public function isBookable($timezone) {

		if (!$this->active) return false;

		$thisClass = get_class($this);
		$now = evavel_date_now()->setTimeZone($timezone)->format('Y-m-d');

		if ($thisClass == Shift::class) {

			// Check last day is greater than today
			if ($this->end_date >= $now) {
				return true;
			}
		}
		else if ($thisClass == Event::class) {

			// Get included dates
			// Check some date is > now
			$dates = $this->include_dates;
			if (is_array($dates)){
				foreach($dates as $date) {
					if ($date >= $now) {
						return true;
					}
				}
			}

		}

		return false;
	}

	/** TESTED
	 * Check date is available
	 *
	 * @param Carbon|String $day
	 *
	 * @return bool
	 */
	public function isDateBookable($day) {

		$thisClass = get_class($this);

		if ($thisClass == Shift::class) {
			return $this->isDateBookableForShift($day);
		}
		else if ($thisClass == Event::class) {
			return $this->isDateBookableForEvent($day);
		}

		return false;
	}

	/** TESTED
	 * Check specific date
	 *
	 * @param Carbon|String $day
	 *
	 * @return bool
	 */
	public function isDateBookableForShift($day) {

		// Get day of the week
		if (is_string($day)) {
			$day_string = $day;
			$day = evavel_date_createFromFormat('Y-m-d', $day);
		} else {
			$day_string = $day->toDateString();
		}
		$dayOfWeek = $day->dayOfWeek;

		// Check the range
		if ($day_string < $this->start_date || $day_string > $this->end_date) {
			return false;
		}

		// Check exclude dates
		if (is_array($this->exclude_dates) && in_array($day_string, $this->exclude_dates)) {
			return false;
		}

		// Check include dates
		if (is_array($this->include_dates) && in_array($day_string, $this->include_dates)) {
			return true;
		}

		// Check week days
		$shifts_days_of_week = $this->days_of_week;
		$weekdays = ['sun', 'mon','tue','wed','thu','fri','sat','sun'];

		if ( $shifts_days_of_week[ $weekdays[$dayOfWeek] ] === false ) {
			return false;
		}

		return true;
	}

	/** TESTED
	 * Check specific date
	 *
	 * @param Carbon|String $day
	 *
	 * @return bool
	 */
	public function isDateBookableForEvent($day) {

		if (is_string($day)) {
			$day_string = $day;
		} else {
			$day_string = $day->toDateString();
		}

		if (is_array($this->include_dates) && in_array($day_string, $this->include_dates)) {
			return true;
		}

		return false;
	}

	/** TESTED
	 * Get list of slots defined as available to book
	 * Does not take into account open/close rules
	 *
	 * @return int[]|string[]
	 */
	public function timeSlotsBookable()
	{
		$all_slots = $this->mapBlockSlots();

		$slots = array_filter($all_slots, function($slot) {
			return $slot === true;
		});

		return array_keys($slots);
	}

	/** TESTED
	 * Simple list of slots given interval
	 * @param $interval
	 *
	 * @return array
	 */
	protected function listSlots($interval) {
		$slots = [];
		$first_seating = $this->first_seating;
		$last_seating = $this->last_seating;

		for($time = $first_seating; $time <= $last_seating; $time += $interval) {
			$slots[$time] = true;
		}

		return $slots;
	}

	/** TESTED
	 * Get all slots in array.
	 * Keys are times, value is true(available), false(blocked)
	 * ex: [ 30600 => true, 37800 => false ]
	 *
	 * @return array
	 */
	public function mapBlockSlots()
	{
		$slots = $this->listSlots($this->interval);

		// Remove block slots
		foreach ($this->block_slots as $b_slot) {
			$slots[$b_slot['time']] = !$b_slot['block'];
		}

		return $slots;
	}


	/** TESTED
	 * Check number of guests are allowed
	 * @param $guests
	 *
	 * @return bool
	 */
	public function isNumberOfCoversAllowed($guests) {
		if ($guests < $this->min_covers_reservation) return false;
		if ($guests > $this->max_covers_reservation) return false;
		return true;
	}

	/** TESTED
	 * Check if specific time is bookable
	 * Does not mean it is available (depends on date and open/close rules)
	 * @param $time
	 *
	 * @return bool
	 */
	public function isTimeSlotBookable($time) {

		if ($time < $this->first_seating) return false;
		if ($time > $this->last_seating) return false;

		// Check it is not blocked
		$slots = $this->mapBlockSlots();

		if (isset($slots[$time]) && $slots[$time] === true) {
			return true;
		}
		return false;
	}

	/** TESTED
	 * Check if date and time is bookable based on the Open/Close rules
	 * @param $date_string
	 * @param $time
	 *
	 * @return bool
	 */
	public function isDateTimeBookable($date_string, $time)
	{
		$list = $this->openCloseWindowForSlots($date_string);

		return (isset($list[$time]) && $list[$time]['available']);
	}


	/** TESTED
	 * Check if has some slot available using the Open/Close rules
	 * @param $booking_date
	 * @param $timezone
	 *
	 * @return bool
	 */
	public function hasSomeSlotBookable($booking_date = null, $timezone = null)
	{
		$slots = $this->openCloseWindowForSlots($booking_date, $timezone, true);
		return count($slots) > 0;
	}

	/* TESTED
	 *  From specific booking date return list of Carbon dates for every slot
	 * [ 52200 => [ available => true, date => Carbon, start => Carbon, end => Carbon ], ... ]
	 *
	 * @param $booking_date
	 * @param $timezone
	 *
	 * @return array
	 */
	public function openCloseWindowForSlots($booking_date = null, $timezone = null, $filter_only_available = true) {

		if (!$timezone) {
			$timezone = $this->getTimezone();
		}

		if ($booking_date == null){
			$booking_date = evavel_now_timezone($timezone)->format("Y-m-d");
		}

		// Be sure date is bookable
		if (!$this->isDateBookable($booking_date)) return [];

		$now = evavel_now_timezone($timezone);

		$slots = $this->mapBlockSlots();

		$result = [];

		foreach($slots as $time => $bookable) {
			if ($bookable === false) continue;

			$open_date = $this->openDate($booking_date, $time, $timezone);
			$close_date = $this->closeDate($booking_date, $time, $timezone);

			$booking_date_time = evavel_carbon($booking_date, $time, $timezone);

			$available = true;
			$message = '';

			if ($open_date > $close_date) {
				$available = false;
				$message = __eva('Open date > Close date');
			}
			else if ($now < $open_date) {
				$available = false;
				$message = __eva('Not open yet');
			}
			else if ($now > $close_date) {
				$available = false;
				$message = __eva('Already closed');
			}
			else if ($now > $booking_date_time) {
				$available = false;
				$message = __eva('Date past');
			}



			// Only time slots that are available
			if (!$filter_only_available || ($filter_only_available && $available)) {
				$result[$time] = [
					'available' => $available,
					'message' => $message,
					'date' => $booking_date_time,
					'start' => $open_date,
					'end' => $close_date
				];
			}

		}

		return $result;
	}


	/** TESTED
	 * Calculate open date
	 * @param $time_slot
	 * @param $timezone
	 *
	 * @return Carbon|false|null
	 */
	protected function openDate($date_string, $time_slot, $timezone) {

		switch ($this->open_reservation_mode) {
			case 'open_all_time':
				return evavel_carbon_2000();
				break;
			case 'open_hours_before':
				return evavel_carbon($date_string, $time_slot, $timezone)->addSeconds(- $this->open_hours_before);
				break;
			case 'open_same_day_at_time':
				return evavel_carbon($date_string, $this->open_same_day_time, $timezone);
				break;
			case 'open_days_before_at_time':
				return evavel_carbon($date_string, $this->open_several_days_time, $timezone)->addDays(- $this->open_several_days_count);
				break;
			default:
				return null;
		}
	}

	/** TESTED
	 * Calculate close date
	 * @param $time_slot
	 * @param $timezone
	 *
	 * @return Carbon|false|null
	 */
	protected function closeDate($date_string, $time_slot, $timezone) {

		switch ($this->close_reservation_mode) {
			case 'until_last_minute':
				return evavel_carbon($date_string, $time_slot, $timezone);
				break;
			case 'until_hours':
				return evavel_carbon($date_string, $time_slot, $timezone)->addSeconds(- $this->until_hours_period);
				break;
			case 'until_same_day':
				return evavel_carbon($date_string, $this->until_same_day_time, $timezone);
				break;
			case 'until_previous_day':
				return evavel_carbon($date_string, $this->until_previous_day_time, $timezone)->addDays(- $this->until_previous_day_count);
				break;
			default:
				return null;
		}
	}

	/** TESTED
	 * Get the key for interval
	 * @param $start_seconds
	 * @param $end_seconds
	 *
	 * @return string
	 */
	protected function intervalKey($start_seconds, $end_seconds) {
		return $start_seconds.'-'.$end_seconds;

		// For debugging
		//return evavel_seconds_to_Hmi($start_seconds).'-'.evavel_seconds_to_Hmi($end_seconds);
	}

	/** TESTED
	 * Get intervals every 15 minutes with number of covers
	 * Get current bookings occupied
	 * Son covers calculados para saber si hay mas sitio
	 * @param $date_string
	 *
	 * @return array
	 */
	public function mapTimeSlotCovers($date_string, $include_selected = true) {

		$interval = $this->stepQuarterOfMinute;
		$slots_covers = $this->prepareArraySlots();

		$list_statuses = BookingStatus::occupied();
		if (!$include_selected) {
			$list_statuses = BookingStatus::occupied_not_selected();
		}

		// Bookings with this shift/event and date
		// Son asientos ocupados en total, no importa el estado
		$bookings = Booking::where('shift_event_id', $this->id)
		                   ->where('date', $date_string)
		                   ->whereIn('status', $list_statuses)
		                   ->get();

		foreach ($bookings as $booking)
		{
			$start_time = $booking->time;
			$end_time = $start_time + $booking->duration;

			for ($time = $start_time; $time < $end_time; $time += $interval) {
				$key = $this->intervalKey($time, $time+$interval);
				if (isset($slots_covers[$key])) {
					$slots_covers[$key] += $booking->party;
				}
			}
		}

		return $slots_covers;
	}

	protected function prepareArraySlots() {
		// Every 15 minutes to take into account duration that can be set with 15 min precision (ex: 1h 45min)
		$interval = $this->stepQuarterOfMinute;
		$slots = $this->listSlots($interval);

		$slots_covers = [];
		$count = 0;
		foreach($slots as $time => $bookable) {
			$count++;
			// SKip last interval. If shift is from 17:00 to 19:00 we don't need interval 19:00-19:15
			if ($count < count($slots)) {
				$key = $this->intervalKey($time, $time+$interval);
				$slots_covers[$key] = 0;
			}
		}
		return $slots_covers;
	}

	public function mapTimeSlotCovers_WithNewCovers($date_string) {

		$interval = $this->stepQuarterOfMinute;

		$slots_covers = $this->mapTimeSlotCovers($date_string, false);
		foreach($slots_covers as $key => $value) {
			$slots_covers[$key] = [
				'total' => $value,
				'new' => 0
			];
		}

		$list_statuses = BookingStatus::occupied_not_selected();

		$bookings = Booking::where('shift_event_id', $this->id)
		                   ->where('date', $date_string)
		                   ->whereIn('status', $list_statuses)
		                   ->get();

		foreach ($bookings as $booking)
		{
			$time = $booking->time;
			$key =  $this->intervalKey($time, $time+$interval);
			if (isset($slots_covers[$key])) {
				$slots_covers[$key]['new'] += $booking->party;
			}

		}

		return $slots_covers;

	}



	/** TESTED
	 * Get duration in seconds depending on the number of covers
	 * @param $num_guests
	 *
	 * @return int
	 */
	public function getDuration($num_guests) {
		$mode = $this->duration_mode;

		switch ($this->duration_mode) {
			case 'time':
				return $this->duration_time;
				break;
			case 'covers':
				$duration = 0;
				foreach ($this->duration_covers as $d_cover){
					if ($num_guests >= $d_cover['label']) {
						$duration = $d_cover['value'];
					}
				}
				return $duration;
				break;
			default:
				return 7200;
		}
	}

	/** TESTED
	 * Check a specific slot, specific date, if it is closed
	 * @param $date_string
	 * @param $time
	 *
	 * @return bool
	 */
	public function isSlotClosed($date_string, $time) {

		$closed_slots = ClosedSlot::where(evavel_tenant_field(), $this->restaurant_id)->first();

		$data = $closed_slots->{$date_string};
		if (!$data) return false;

		foreach($data as $item){
			if (isset($item['id']) && $item['id'] == $this->id){
				if (in_array($time, $item['slots'])){
					return true;
				}
			}
		}

		return false;
	}

	/** TESTED
	 * Check if booking is allowed using all the parameters and rules
	 * @param $date_string
	 * @param $guests
	 * @param $time
	 *
	 * @return bool
	 */
	public function isAvailable($date_string, $time, $guests) {


		// Check date is bookable and rules open/close
		if (!$this->isDateTimeBookable($date_string, $time)) return false;

		//ray('isAvailable ' . $date_string.' ' . $time. ' ' .$guests. ' - ' . evavel_seconds_to_Hm($time));

		// IMPORTANTE ++++++++++++++++++++++++++++++++++++++++++++++++++++++
		$requireSingleTablesToBeOnlineForGroups = false;

		// Based on availability type the check is different
		switch ($this->availability_type)
		{
			case self::AVAILABILITY_ALL_TABLES:
				if (method_exists($this, 'isAvailableBy_SearchTables')){
					$tables = $this->isAvailableBy_SearchTables($date_string, $time, $guests, $requireSingleTablesToBeOnlineForGroups);
					return $tables;
				}

				return $this->isAvailableByTables_MaxCovers($date_string, $time, $guests, false);
				break;

			case self::AVAILABILITY_SPECIFIC_TABLES:
				if (method_exists($this, 'isAvailableBy_SearchTables')){
					$tables = $this->isAvailableBy_SearchTables($date_string, $time, $guests, $requireSingleTablesToBeOnlineForGroups);
					return $tables;
				}

				return $this->isAvailableBySpecificTables_MaxCovers($date_string, $time, $guests);
				break;

			case self::AVAILABILITY_VOLUME_TOTAL:
				return $this->isAvailableByVolumeTotal($date_string, $time, $guests);
				break;

			case self::AVAILABILITY_VOLUME_SLOTS:
				return $this->isAvailableByVolumeSlots($date_string, $time, $guests);
				break;

			default:
				return false;
		}
	}


	/** TESTED
	 * Check if booking is allowed based on all the tables online
	 * Calculate the total covers for all tables and current bookings
	 * @param $date_string
	 * @param $time
	 * @param $guests
	 *
	 * @return bool
	 */
	protected function isAvailableByTables_MaxCovers($date_string, $time, $guests)
	{
		$max_online_covers = intval( $this->compute('tables_max_online_covers') );

		$total_covers = $this->bookingsTotalCovers($date_string);

		//ray($total_covers.' / '.$max_online_covers);

		return $max_online_covers >= ($total_covers + $guests);
	}

	/** TESTED
	 * Check if booking is allowed based on specific tables selected
	 * Calculate the total covers for tables and current bookings
	 * @param $date_string
	 * @param $time
	 * @param $guests
	 *
	 * @return bool
	 */
	protected function isAvailableBySpecificTables_MaxCovers($date_string, $time, $guests)
	{
		$max_covers = $this->maxCoversForEspecificTables();

		$total_covers = $this->bookingsTotalCovers($date_string);

		return $max_covers >= ($total_covers + $guests);
	}

	/** TESTED
	 * Check if booking is allowed based on max covers
	 * Compare with the max cover allowed and the current bookings
	 * @param $date_string
	 * @param $time
	 * @param $guests
	 *
	 * @return bool
	 */
	protected function isAvailableByVolumeTotal($date_string, $time, $guests)
	{
		$total_covers = $this->bookingsTotalCovers($date_string);

		return ($total_covers + $guests) <= $this->availability_total;
	}

	/** TESTED
	 * Check if booking is allowed based on max covers allowed per slot
	 * @param $date_string
	 * @param $time
	 * @param $guests
	 *
	 * @return bool
	 */
	protected function isAvailableByVolumeSlots($date_string, $time, $guests)
	{
		$result = false;

		$duration = $this->getDuration($guests);

		$start_time = $time;
		$end_time = $time + $duration;


		// COMPROBAR ASIENTOS TOTALES DEL INTERVALO =============================================================
		// Occupancy of covers for each slot
		$mapCovers = $this->mapTimeSlotCovers($date_string);
		//ray($date_string.' - '.$time.' - '.$guests);
		//ray($mapCovers);

		// Extract the intervals needed for the booking with the covers used per interval
		$current_covers = [];
		for ($seconds = $start_time; $seconds < $end_time; $seconds += $this->stepQuarterOfMinute)
		{
			$key = $this->intervalKey($seconds, $seconds + $this->stepQuarterOfMinute);

			if (isset($mapCovers[$key])) {
				$current_covers[$key] = $mapCovers[$key];
			}
			// ERROR. some interval not defined so not available
			else {
				return false;
			}
		}

		/* I have all the intervals with covers used
		[
		  "54000-54900" => 6
		  "54900-55800" => 6
		  "55800-56700" => 6
		  "56700-57600" => 6
		  "57600-58500" => 2
		  "58500-59400" => 2
		  "59400-60300" => 0
		]
		*/

		// I need to compare with the setting Covers per slot
		$list_allowed_covers = $this->getCoversAvailability();
		//ray($list_allowed_covers);
		//ray($current_covers);


		// Compare max allowed with (current covers + needed covers)
		// key: '36000-36900' , '36900-37800', etc
		$count_success = 0;
		foreach ($current_covers as $key => $current_num) {
			if (isset($list_allowed_covers[$key])) {
				$max_covers = $list_allowed_covers[$key];
				if ($max_covers >= ($current_num + $guests)){
					$count_success ++;
				}
			}
		}

		// Is all intervals are ok then booking is ok
		if ($count_success > 0 && $count_success == count($current_covers)) {
			$result = true;
		}


		// COMPROBAR ASIENTOS NUEVOS DEL INTERVALO =============================================================
		// Comprobar nuevos seats permitidos por slot solo si esta activado
		$check_new_covers = $this->availability_slots_limit_new_covers;
		if ($check_new_covers != 1){
			return $result;
		}

		// Estos son los asientos nuevos maximos, me los da cada 15 minutos, aunque en realidad los intervalos sean de mas tiempo
		$list_max_new_covers = $this->getCoversAvailability('new_covers');

		// Lo maximos a coger son los del intervalo $time-$time+900
		$max_new_covers_allowed = $list_max_new_covers[$time.'-'.($time+$this->stepQuarterOfMinute)];

		// Ese maximo valor hay que compararlo con las reservas nuevas recibidas durante el intervalo completo
		// time-time+interval
		$bookings = Booking::where('restaurant_id', $this->restaurant_id)
		                   ->where('shift_event_id', $this->id)
		                   ->where('date', $date_string)
		                   ->whereIn('status', BookingStatus::occupied())
		                   ->where('time', '>=', $time)
		                   ->where('time', '<', $time + $this->interval)
		                   ->get();

		// Obtener los asientos de esas reservas
		$new_seats_booked = 0;
		foreach($bookings as $booking) {
			$new_seats_booked += $booking->party;
		}
		//ray('Date: ' . $date_string.' : ' .$time/3600 . '-------------------------------------------');
		//ray('New seats booked: ' . $new_seats_booked);
		//ray('Guests: ' . $guests);
		//ray('Max new allowed: ' . $max_new_covers_allowed);

		$result = ($new_seats_booked + $guests) <= $max_new_covers_allowed;

		return $result;
	}

	/** TESTED
	 * Get Total covers in bookings for date with this shift
	 * @param $date_string
	 *
	 * @return int
	 */
	protected function bookingsTotalCovers($date_string)
	{
		$bookings = Booking::where('shift_event_id', $this->id)
		                   ->where('date', $date_string)
		                   ->whereIn('status', BookingStatus::occupied())
		                   ->get();
		$total_covers = 0;
		foreach($bookings as $booking){
			$total_covers += $booking->party;
		}

		return $total_covers;
	}

	/** TESTED
	 * Transform number of covers per interval in a list of intervals of 900 seconds
	 * so can be compared with the mapTimeSlotsCovers
	 * @return array
	 */
	protected function getCoversAvailability( $variable = 'covers' )
	{
		$list = [];

		$items = $this->availability_slots;
		$interval = $this->interval;

		for($i = 0; $i < count($items); $i++)
		{
			$time_start = $items[$i]['time'];
			$time_end = $time_start + $interval;
			$covers = $items[$i][$variable];

			for ($seconds = $time_start; $seconds < $time_end; $seconds += $this->stepQuarterOfMinute){
				$key = $this->intervalKey($seconds, $seconds+$this->stepQuarterOfMinute);
				$list[$key] = $covers;
			}
		}

		return $list;
	}


	// Return slots available for a specific date
	public function getSlotsAvailable($guests, $date, $time, $numberOfSlotsToGet = 4) {

		/* PROCESS
		Check the $date,$guests,$time is allowed by this shift
		1. For that date calculate all slots for each shift
		2. Filter shift rule -> blocked slots
		3. Filter shift rule duration (depends on guests)
		. Filter based on shift Open rule
		. Filter based on shift Close rule
		. Filter shift rule Covers
		. Based on bookings, and shift rules remove slots not available
		8. Return some slots above and below the selected time

		I can use filters to remove slots with every filter
		*/

		// Full range of slots
		$slots = [];
		for ($seconds = $this->first_seating; $seconds < $this->last_seating; $seconds += $this->interval){
			if ($this->isAvailable($date, $seconds, $guests)) {
				$slots[] = $seconds;
			}
		}


		$filters = [
			//new FilterByBlockSlots, // NOT needed
			new FilterByDuration,
			new FilterByNearestSlots
		];

		foreach($filters as $filter) {
			$slots = $filter->apply($this, $slots, $guests, $date, $time, $numberOfSlotsToGet);
		}

		return $slots;
	}


	// ESTE ES LA FUNCION PARA AVERIGUAR LOS SLOTS DISPONIBLES
	/** TESTED
	 * Get all slots available based only on date and guests
	 * @param $guests
	 * @param $date
	 *
	 * @return array
	 */
	public function getAllSlotsAvailable($guests, $date)
	{
		//ray('getAllSlotsAvailable for '.$guests.' guests and date ' . $date);

		// Full range of slots
		$slots = [];
		for ($seconds = $this->first_seating; $seconds <= $this->last_seating; $seconds += $this->interval)
		{
			if ($this->isAvailable($date, $seconds, $guests))
			{
				$slots[] = $seconds;
			}
		}

		$filters = [
			new FilterByDuration,
			new FilterByMinMaxGuests,
			new FilterByClosedSlots
		];

		foreach($filters as $filter) {
			$slots = $filter->apply($this, $slots, $guests, $date, 0);
		}


		return $slots;
	}

	// All slots bookable for specific date
	public function getAllBookableSlots($date)
	{
		$slots = [];
		for ($seconds = $this->first_seating; $seconds <= $this->last_seating; $seconds += $this->interval)
		{
			if ($this->isDateTimeBookable($date, $seconds)){
				$slots[] = $seconds;
			}
		}
		return $slots;
	}

	public function availableDates($timezone = 'America/New_York', $days_in_advance = 15)
	{
		$days_available = [];
		for ($i = 0; $i <= $days_in_advance; $i++)
		{
			$date = evavel_date_now()->setTimezone($timezone)->addDays($i);
			$date_string = $date->format('Y-m-d');

			if ($this->hasSomeSlotBookable($date_string, $timezone)){
				$days_available[] = $date->format('Y-m-d');
			}
		}

		// I have to remove closedDays
		$closedDays = ClosedDay::where(ClosedDay::$pivot_tenant_field, $this->restaurant_id)->first();
		if ($closedDays) {
			$days_available = $closedDays->removeDatesFromSimpleArray($days_available);
		}

		return $days_available;
	}

	protected function maxCoversForEspecificTables()
	{
		$tables_included = $this->list_of_tables;

		if (is_array($tables_included))
		{
			$tables = Table::where('restaurant_id', $this->restaurant_id)->get();

			$max_covers = 0;
			foreach($tables as $table){
				if (in_array($table->id, $tables_included)){
					$max_covers += intval($table->max_seats);
				}
			}

			return $max_covers;
		}

		return 0;
	}

	public function totalCovers()
	{
		switch ($this->availability_type)
		{
			case 'tables':
				return intval( $this->compute('tables_max_total_covers') );
				break;
			case 'specific_tables':
				return $this->maxCoversForEspecificTables();
				break;
			case 'volume_total':
				return $this->availability_total;
				break;
			case 'volume_slots':
				$slots = $this->availability_slots;
				if (is_array($slots)){
					$count = 0;
					foreach ($slots as $slot){
						$count += intval($slot['covers']);
					}
					return $count;
				}
				return 0;
				break;
			default:
				return 0;
		}
	}

	/**
	 * Count number of tables based on avaiability type
	 * @return int|string
	 */
	public function totalTables()
	{
		switch ($this->availability_type)
		{
			case 'tables':
				$tables = Table::where('restaurant_id', $this->restaurant_id)->get();
				return count($tables);
				break;
			case 'specific_tables':
				$tables_included = $this->list_of_tables;
				if (is_array($tables_included)){
					return count($tables_included);
				}
				return 0;
				break;
			case 'volume_total':
				return '-';
				break;
			case 'volume_slots':
				return '-';
				break;
			default:
				return 0;
		}
	}


	public function getBookingStatusForNewReservation($date, $time, $party, $email)
	{
		// Si ha puesto confirmed para todos
		if ($this->booking_status == 'confirmed') {
			return BookingStatus::BOOKED;
		}

		// Si se ha puesto pending para todos
		if ($this->booking_status == 'pending') {
			return BookingStatus::PENDING;
		}

		// Confirmed then pending with rules
		$status = BookingStatus::BOOKED;

		$rules = [
			'rulePendingAfterXConfirmed',
			'rulePendingMoreThanXSeats',
			'rulePendingForSpecificSlots',
			'rulePendingForCustomers',
			'rulePendingForSpecificDays'
		];

		// If some rule is pending then return pending
		foreach($rules as $rule) {
			$status = $this->{$rule}($date, $time, $party, $status, $email);
			if ($status == BookingStatus::PENDING) return $status;
		}

		return $status;
	}

	public function rulePendingAfterXConfirmed($date, $time, $party, $status, $email)
	{
		// Check if rule is enabled
		if ($this->rule_bookings_enable !== true) {
			return $status;
		}

		// Get all bookings confirmed or reconfirmed
		$bookings = Booking::where(evavel_tenant_field(), $this->restaurant_id)
		                   ->where('shift_event_id', $this->id)
		                   ->where('date', $date)
		                   ->whereIn('status', [BookingStatus::BOOKED, BookingStatus::CONFIRMED])
		                   ->get();

		$count = 0;
		foreach($bookings as $booking){
			$count += intval($booking->party);
		}

		$max = intval($this->status_confirmed_covers);

		if ($max > 0) {
			if ( ($count + $party) > $max) {
				return BookingStatus::PENDING;
			}
		}

		return $status;
	}

	public function rulePendingMoreThanXSeats($date, $time, $party, $status, $email)
	{
		// Check if rule is enabled
		if ($this->rule_seats_enable !== true) {
			return $status;
		}

		$seats = intval($this->status_seats_pending);

		if ($seats > 0){
			if (intval($party) >= $seats) {
				return BookingStatus::PENDING;
			}
		}

		return $status;
	}

	public function rulePendingForSpecificSlots($date, $time, $party, $status, $email)
	{
		// Check if rule is enabled
		if ( $this->rule_slots_enable !== true ) {
			return $status;
		}

		$list = $this->status_per_slot;
		if (!is_array($list)) return $status;

		foreach($list as $item)
		{
			if ($item['time'] == $time)
			{
				return $item['pending'] == true ? BookingStatus::PENDING : $status;
			}
		}

		return $status;
	}

	public function rulePendingForCustomers($date, $time, $party, $status, $email)
	{
		// Check tags selected and then customers with those tags
		// If customer email fits email then put as pending

		// Check if rule is enabled
		if ( $this->rule_customers_enable !== true ) {
			return $status;
		}

		$tags_id = $this->status_tags_customers;
		if (!$tags_id || empty($tags_id)) {
			return $status;
		}

		$customer = Customer::where('email', $email)->first();
		$customer_tags_id = $customer->tags->pluck('id');
		if (!$customer_tags_id || empty($customer_tags_id)) {
			return $status;
		}

		foreach($tags_id as $tag_id) {
			if (in_array($tag_id, $customer_tags_id)) {
				return BookingStatus::PENDING;
			}
		}

		return $status;
	}

	public function rulePendingForSpecificDays($date, $time, $party, $status, $email)
	{
		// Check if rule is enabled
		if ($this->rule_exclude_days_enable !== true) {
			return $status;
		}

		$days = $this->status_exclude_days;
		if (!$days || empty($days) || !is_array($days)) {
			return $status;
		}

		if (in_array($date, $days)) {
			return BookingStatus::PENDING;
		}

		return $status;
	}

	function applyRulePendingForSpecificTables($booking)
	{
		// If already pending then skip
		if ($booking->status == BookingStatus::PENDING) return;

		// It no tables selected then no need to check
		$tables_list = $booking->tablesList;
		if (!is_array($tables_list)) return;

		// Check if rule is enabled
		if ($this->rule_exclude_tables_enable !== true) return;

		$tables_exclude = $this->status_exclude_tables;
		if (!$tables_exclude || empty($tables_exclude) || !is_array($tables_exclude)) return;

		// Check if tables is included
		$is_included = false;
		foreach($tables_list as $table_id){
			if (in_array($table_id, $tables_exclude)){
				$is_included = true;
			}
		}

		if ($is_included){
			$booking->status = BookingStatus::PENDING;
		}
	}


	public function showServiceDurationInWidget($widget_id)
	{
		// Default widget setting
		$widget_form = \Alexr\Settings\WidgetForm::where('id', $widget_id)->first();
		if (!$widget_form) return false;
		$show_services_duration = isset($widget_form->form_config['show_services_duration']) ? $widget_form->form_config['show_services_duration'] : 'yes';
		$show_services_duration = $show_services_duration == 'no' ? false : true;

		// Custom shift setting
		$widget_custom = $this->widget_custom;
		if (!$widget_custom) return $show_services_duration;

		// Check is enabled overwrite general
		if (!isset($widget_custom['overwrite_general'])) return null;
		$overrite_general = $widget_custom['overwrite_general'];
		if ($overrite_general != '1' && $overrite_general != 1 && $overrite_general != 'true' && $overrite_general !== true) {
			return $show_services_duration;
		}

		if (!isset($widget_custom['show_duration'])) return $show_services_duration;
		return $widget_custom['show_duration'] == 'yes';
	}


	//======================================================================================
	// Next funcions are OLD stuff
	// @TODO (OLD) refactor with the new functions
	public function calculateMaxDaysInAdvance()
	{
		// If not active do not take into account
		if (!$this->isBookable()) return -1;

		$maxDaysInAdvance = 0;

		// Check last day of the period
		$today = evavel_date_now();
		$endDate = evavel_date_createFromFormat('Y-m-d H:i:s', $this->end_date.' 23:59:59');

		$diffDays = $endDate->diffInDays(evavel_date_now());
		$isFuture = $today <= $endDate;

		if($isFuture) {
			$maxDaysInAdvance = $diffDays;
		}

		// Check the opening setting
		$open_reservation_mode = $this->open_reservation_mode;

		switch ($open_reservation_mode) {
			case 'open_all_time':
				// Do nothing
				break;
			case 'open_hours_before':
				$seconds = $this->open_hours_before;
				$days = intval($seconds/86400);
				if ($days < $maxDaysInAdvance) {
					$maxDaysInAdvance = $days;
				}
				break;
			case 'open_same_day_at_time':
				$maxDaysInAdvance = 0;
				break;
			case 'open_days_before_at_time':
				if ($this->open_several_days_count < $maxDaysInAdvance){
					$maxDaysInAdvance = $this->open_several_days_count;
				}
				break;
			default:
				break;
		}

		return $maxDaysInAdvance;
	}


	/**
	 * Calculate covers available when selecting areas in availability_type = volume_total
	 * @param $date_string
	 * @param $time
	 * @param $guests
	 *
	 * @return array
	 */
	public function getMaxCoversPerArea($date_string, $time, $guests)
	{
		// Get areas
		// MAX: Get max values set in the shift when availability type
		// OCCUPIED: Get bookings and count seats for each area when status is confirmed

		$setting = $this->getSettingsMaxCoversPerArea();

		$areas = Area::where('restaurant_id', $this->restaurant_id)->where('bookable_online',1)->get();
		$list = [];

		$required_time_start = $time;
		$required_time_end = $required_time_start + $this->getDuration($guests);

		foreach($areas as $area) {

			// Get seats used for this area selected
			$bookings = Booking::where('restaurant_id', $this->restaurant_id)
			                   ->where('shift_event_id', $this->id)
			                   ->where('date', $date_string)
			                   ->where('area_selected_id', $area->id)
			                   ->whereIn('status', BookingStatus::occupied())
			                   ->get();

			$occupied = 0;
			foreach($bookings as $booking){
				$booking_start_time = $booking->time;
				$booking_end_time = $booking->time + $booking->duration;
				if ( alexr_is_range_overlapping($required_time_start, $required_time_end, $booking_start_time, $booking_end_time) )
				{
					$occupied += intval($booking->party);
				}
			}

			$max_covers = isset($setting['area-'.$area->id]) ? $setting['area-'.$area->id] : 0;

			$list[] = [
				'area_id' => $area->id,
				'max_covers' => $max_covers, // Viene de los settings
				'occupied' => $occupied
			];

		}

		return $list;

		/*
		    [
				['area_id' => 6, 'max_covers' => 50, 'occupied' => 48],
				['area_id' => 13, 'max_covers' => 20, 'occupied' => 19],
			]
		*/
	}

	/**
	 * Use when availability_type = volume_total
	 * Get settings for max covers per area
	 * @return int[]
	 */
	public function getSettingsMaxCoversPerArea()
	{
		// area_id:max , area_id:max
		/*
		  [ "area" => 6, "value" => "45" ],
		  [ "area" => 13, "value" => "12" ]
		*/

		$values = $this->covers_can_select_area_maxperarea; // 6:30,13:20

		if (is_array($values))
		{
			$list = [];
			foreach($values as $arr)
			{
				$list['area-'.$arr['area']] = intval($arr['value']);
			}

			return $list;
			/* [
				'area-6' => 50,
				'area-13' => 45,
			]*/
		}

		return null;
	}
}
