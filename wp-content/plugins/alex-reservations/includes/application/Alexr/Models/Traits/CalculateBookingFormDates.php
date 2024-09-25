<?php

namespace Alexr\Models\Traits;

use Alexr\Settings\ClosedDay;
use Alexr\Settings\Event;
use Alexr\Settings\General;
use Alexr\Settings\Shift;
use Alexr\Settings\WidgetForm;
//use Carbon\Carbon;

trait CalculateBookingFormDates {

	// -1 => is not bookable, use -2 to be able to cache the result
	public $days_in_advance = -2;

	//------------------------------------------------------
	// HELPERS - Get shifts and events from the widget
	//------------------------------------------------------

	/** TESTED
	 * Get ALL restaurant active shifts
	 *
	 * @return mixed
	 */
	public function getActiveShifts()
	{
		return Shift::where('restaurant_id', $this->id)
	                ->get()
	               ->filter(function($shift){
					   return $shift->active == 1;
				   });
	}

	/** TESTED
	 * Get ALL restaurant active events
	 *
	 * @return mixed
	 */
	public function getActiveEvents()
	{
		return Event::where('restaurant_id', $this->id)
		            ->get()
		            ->filter(function($event){
			            return $event->active == 1;
		            });
	}


	/** TESTED
	 * Get Widget Form active shifts
	 *
	 * @param $widget_id
	 *
	 * @return array
	 */
	public function getShifts($widget_id)
	{
		$w_form = WidgetForm::where('id', $widget_id)->first();
		if (!$w_form) return [];

		$shifts_option = $w_form->form_config['shifts_option'];

		if ($shifts_option == 'all') {
			return Shift::where('restaurant_id', $this->id)
				->orderBy('ordering', 'ASC')
	            ->get()
	            ->filter(function($shift){
		            return $shift->active == 1;
	            });
		}

		$ids = $w_form->form_config['shifts'];

		return Shift::whereIn('id', $ids)
			->orderBy('ordering', 'ASC')
            ->get()
			->filter(function($shift){
				return $shift->active == 1;
			});
	}

	/** TESTED
	 * Get Widget Form active events
	 *
	 * @param $widget_id
	 *
	 * @return array
	 */
	public function getEvents($widget_id)
	{
		$w_form = WidgetForm::where('id', $widget_id)->first();
		if (!$w_form) return [];

		$events_option = $w_form->form_config['events_option'];

		if ($events_option == 'all') {
			return Event::where('restaurant_id', $this->id)
				->orderBy('ordering', 'ASC')
				->get()
	            ->filter(function($event){
		            return $event->active == 1;
	            });
		}

		$ids = $w_form->form_config['events'];

		return Event::whereIn('id', $ids)
			->orderBy('ordering', 'ASC')
            ->get()
            ->filter(function($event){
	            return $event->active == 1;
            });
	}

	/** TESTED
	 * Get Widget Form active shifts and events
	 *
	 * @param $widget_id
	 *
	 * @return array
	 */
	public function getShiftsAndEvents($widget_id)
	{
		$shifts = $this->getShifts($widget_id);
		$events = $this->getEvents($widget_id);

		$all = [];
		foreach($shifts as $shift) { $all[] = $shift; }
		foreach ($events as $event) { $all[] = $event; }

		return $all;
	}

	/**
	 * Get a simple list of shifts/events included in the Widget
	 * @param $widget_id
	 *
	 * @return array
	 */
	public function getServices($widget_id)
	{
		$shifts = $this->getShiftsAndEvents($widget_id);

		$list = [];

		foreach($shifts as $shift) {
			$list[] = [
				'id' => $shift->id,
				'name' => $shift->name,
				'public_notes' => $shift->public_notes,
				'translations' => $shift->translations
			];
		}

		return $list;
	}


	//------------------------------------------------------
	// STEP 1 - Present guests, date, time
	//------------------------------------------------------

	/** TESTED
	 * Check if Widget form is bookable
	 * Is bookable if at least 1 shift or event is bookable
	 * (only checks the dates available to book)
	 *
	 *
	 * @param $widget_id
	 *
	 * @return bool
	 */
	public function isBookable($widget_id)
	{
		$count_not_bookable = 0;
		$shifts = $this->getShiftsAndEvents($widget_id);

		foreach($shifts as $shift) {
			if (!$shift->isBookable($this->timezone)) {
				$count_not_bookable++;
			}
		}

		return count($shifts) > $count_not_bookable;
	}

	/** TESTED
	 * Check date is bookable
	 * Does not take into account rules for opening, closing
	 * Just if the date is allowd to be booked
	 *
	 * @param WidgetForm $widget_id
	 * @param String $date_string
	 *
	 * @return bool
	 */
	public function isDateBookable($widget_id, $date_string)
	{
		$shifts = $this->getShiftsAndEvents($widget_id);

		foreach($shifts as $shift) {
			if ($shift->isDateBookable($date_string)) {
				return true;
			}
		}

		return false;
	}

	/** TESTED
	 * Get min and max guests allowed
	 *
	 * @param $widget_id
	 *
	 * @return array
	 */
	public function guestsMinMax($widget_id)
	{
		$all = $this->getShiftsAndEvents($widget_id);

		$min_guests = 0;
		$max_guests = 0;

		foreach ($all as $item){

			$min = intval($item->min_covers_reservation);
			$max = intval($item->max_covers_reservation);

			if ($min_guests == 0) $min_guests = $min;
			if ($max_guests == 0) $max_guests = $max;

			if ($min < $min_guests) {
				$min_guests = $min;
			}
			if ($max > $max_guests) {
				$max_guests = $max;
			}
		}

		return [ 'min' => $min_guests, 'max' => $max_guests];
	}

	/** TESTED
	 * Get list of time slots allowed
	 *
	 * @param $widget_id
	 *
	 * @return array
	 */
	public function timeSlotsBookable($widget_id)
	{
		$all = $this->getShiftsAndEvents($widget_id);

		$slots = [];

		foreach ($all as $item) {
			$item_slots = $item->timeSlotsBookable();
			foreach($item_slots as $time){
				$slots[] = $time;
			}
		}

		// Unique values sorted
		$slots = array_unique($slots);
		asort($slots);

		return array_values($slots);
	}

	/** @TODO pending to complete, should check base on open/close rules
	 * Checking if date is bookable
	 * Not taking into account open/close rules
	 * @param $widget_id
	 *
	 * @return array
	 */
	public function availableDates($widget_id, $return_with_shifts = false)
	{
		$w_form = WidgetForm::where('id', $widget_id)->first();
		if (!$w_form) return [];

		$shifts = $this->getShiftsAndEvents($widget_id);
		if (empty($shifts)) return [];

		// Define number of days in advance that can be used
		$days_in_advance = $w_form->form_config['max_days_in_advance'];

		// Simple array with dates
		$days_available = [];

		// Array where keys are dates and value are shifts available
		$days_available_with_shifts = [];

		// Can be up to 360 days
		for ($i = 0; $i <= $days_in_advance; $i++)
		{
			//$date = Carbon::now()->setTimezone($this->timezone)->addDays($i);
			$date = evavel_date_now()->setTimezone($this->timezone)->addDays($i);

			$date_string = $date->format('Y-m-d');

			$is_available = false;
			$shifts_available = [];

			foreach($shifts as $shift)
			{
				if ($shift->hasSomeSlotBookable($date_string, $this->timezone)) {
					$is_available = true;
					$shifts_available[] = $shift->id;
				}
			}

			if ($is_available){
				$days_available[] = $date->format('Y-m-d');
				$days_available_with_shifts[$date->format('Y-m-d')] = $shifts_available;
			}
		}

		// I need to filter closedDays now
		$closedDays = ClosedDay::where(ClosedDay::$pivot_tenant_field, $this->id)->first();
		if ($closedDays) {
			$days_available = $closedDays->removeDatesFromSimpleArray($days_available);
			$days_available_with_shifts = $closedDays->removeDatesAssociatedArray($days_available_with_shifts);
		}

		if ($return_with_shifts) {
			return $days_available_with_shifts;
		}

		return $days_available;
	}

	/** Tested
	 * Check agains the list of days closed
	 * @param $date_string
	 *
	 * @return bool
	 */
	public function isDateClosed($date_string)
	{
		$closedDays = ClosedDay::where(ClosedDay::$pivot_tenant_field, $this->id)->first();
		if ($closedDays == null) return false;

		$dates = $closedDays->dates;

		if ($dates == null) return false;

		if (!is_array($dates)) return false;

		return in_array($date_string, $dates);
	}

	/** TESTED
	 * Get the events to display at the front form
	 * // I need the days that are available, I do not want to display other days not available
	 * @param $widget_id
	 * @param $daysAvailable
	 *
	 * @return array
	 */
	public function getEventsToDisplayInCalendar($widget_id, $daysAvailable = [])
	{
		if (empty($daysAvailable)) return [];

		$events = $this->getEvents($widget_id);
		if (empty($events)) return [];

		$list = [];
		foreach($events as $event) {
			$days = [];
			foreach($daysAvailable as $day){
				if ($event->isDateBookable($day)){
					$days[] = $day;
				}
			}
			if (count($days) > 0){
				$list[] = ['days' => $days, 'color' => $event->color, 'name' => $event->name];
			}
		}

		return $list;
	}

	// @TODO (OLD STUFF)

	/** NO USAR
	 * Any date after daysInAdvance will be greyed in the calendar
	 *
	 * @param $widget_id
	 *
	 * @return int|mixed
	 */
	/*public function getForm_daysInAdvance($widget_id)
	{
		// Cache result
		if ($this->days_in_advance > -2) {
			return $this->days_in_advance;
		}


		//For every Shift I have to check:
		//- Range of dates -> max days in advance (put max 90 for the restaurant (global setting) )
		//- Setting opening -> days in advance (more limiting)

		//Get the max values between all shifts

		$shifts = $this->getShiftsAndEvents($widget_id);

		// Get a list of days in advance for every shift
		$list_maxDaysInAdvance = [];
		foreach($shifts as $shift) {
			$list_maxDaysInAdvance[] = $shift->calculateMaxDaysInAdvance();
		}

		$max = -2; // -1 means shift is not available to book
		foreach($list_maxDaysInAdvance as $max_days) {
			if ($max_days > $max) {
				$max = $max_days;
			}
		}

		// Cannot be rented
		if ($max == -1) {
			$this->days_in_advance = $max;
			return $this->days_in_advance;
		}

		// Be sure it is ok with the general setting
		$max_for_restaurant = 90;
		$w_form = WidgetForm::where('restaurant_id', $this->id)->first();
		if ($w_form) {
			$max_for_restaurant = $w_form->form_config['max_days_in_advance'];
		}
		if ($max > $max_for_restaurant) {
			$max = $max_for_restaurant;
		}

		$this->days_in_advance = $max;

		return $this->days_in_advance;
	}*/

	/** NO USAR
	 * Useful for the calendar to know the max month that can display
	 * (aka press next month button)
	 *
	 * @param $widget_id
	 *
	 * @return string
	 */
	/*public function getForm_maxDateToDisplayMonth($widget_id)
	{
		// This should come from days in advance
		$daysInAdvance = $this->getForm_daysInAdvance($widget_id);
		return Carbon::now()->addDays($daysInAdvance)->toDateString();
	}*/

	/** NO USAR
	 * Base on the daysInAdvance calculate each date if can be bookable
	 *
	 * @param $widget_id
	 *
	 * @return array
	 */
	/*public function getForm_skipDates($widget_id)
	{
		// Iterate day by day and check if it is blocked by all active shifts
		// List all days in advance from today
		// Assign 0 value to each date
		// Iterate every active Shift
		// add +1 if that day is blocked
		// Filter dates in the list that has a value == number of Shifts

		$days_in_advance = $this->getForm_daysInAdvance($widget_id);

		$list_days = [];
		for($i = 0; $i <= $days_in_advance; $i++){
			$list_days[] = Carbon::now()->addDays($i);
		}

		$shifts = $this->getShiftsAndEvents($widget_id);

		$list_days_count_blocked = [];
		foreach($list_days as $day) {

			$day_string = $day->toDateString();
			$list_days_count_blocked[$day_string] = 0;

			foreach($shifts as $shift) {
				if (!$shift->isDateBookable($day)) {
					$list_days_count_blocked[$day_string] += 1;
				}
			}
		}

		$final_list = [];
		foreach($list_days_count_blocked as $date_string => $count) {
			if ($count == count($shifts)) {
				$final_list[] = $date_string;
			}
		}

		return $final_list;
	}*/




	//------------------------------------------------------
	// STEP 2 - Slots available
	//------------------------------------------------------

	/**
	 * Slots for a specific date
	 * This funcion is called for other dates too, so I need to overwrite numberOfSlots
	 * @param $guests
	 * @param $date
	 * @param $time
	 * @param $widget_id
	 * @param $numberOfSlotsToGet
	 *
	 * @return array
	 */
	public function getSlotsAvailable($guests, $date, $time, $widget_id, $numberOfSlotsToGet = null)
	{
		// Get all shifts and events
		$shifts = $this->getShiftsAndEvents($widget_id);

		if (!$numberOfSlotsToGet) {
			$numberOfSlotsToGet = $this->numberOfSlotsForShiftOrEvent($widget_id);
		}

		$list = [];
		foreach($shifts as $shift) {
			$slots = $shift->getSlotsAvailable($guests, $date, $time, $numberOfSlotsToGet);
			foreach($slots as $slot) {
				$list[] = [
					'time' => $slot,
					'name' => $shift->name,
					'id' => $shift->id
				];
			}
		}

		return $list;
	}

	/** TESTED
	 * Slots to display for other dates
	 * @param $guests
	 * @param $date
	 * @param $time
	 * @param $widget_id
	 * @param $numberOfSlotsToGet
	 *
	 * @return array
	 */
	public function getOtherDaysSlotsAvailable($guests, $date, $time, $widget_id, $numberOfSlotsToGet = null)
	{
		$days_slots = $this->getOtherDaysNumberOfSlots($widget_id);
		$number_days = $days_slots['number_days'];
		$number_of_slots = $days_slots['number_of_slots'];
		if ($number_days == 0) return [];

		$result = [];
		$add_days = 1;
		$days_skipped = 0;

		$w_form = WidgetForm::where('id', $widget_id)->first();
		if (!$w_form) return [];

		$maxDaysInAdvance = $w_form->form_config['max_days_in_advance'];

		do {
			//$otherDate = Carbon::createFromFormat('Y-m-d', $date)->addDays($add_days);
			$otherDate = evavel_date_createFromFormat('Y-m-d', $date)->addDays($add_days);

			$slots = $this->getSlotsAvailable($guests, $otherDate->toDateString(), $time, $widget_id, $number_of_slots);

			if (!empty($slots)){
				$result[] = [
					'date' => $otherDate->toDateString(),
					'slots' => $slots
				];
			} else {
				$days_skipped ++;
			}

			$add_days++;

		} while ( ($add_days-1) <= $maxDaysInAdvance && count($result) < $number_days && $days_skipped < $maxDaysInAdvance);

		return $result;
	}

	/** TESTED
	 * Get the number of slots for the selected date that I want to show to the customer
	 * @param $widget_id
	 *
	 * @return int
	 */
	protected function numberOfSlotsForShiftOrEvent($widget_id)
	{
		$w_form = WidgetForm::where('id', $widget_id)->first();
		if (!$w_form) return 4;

		return intval($w_form->form_config['number_slots_display']);
	}


	/** TESTED
	 * Get number of slots to display for additional days
	 * @param $widget_id
	 *
	 * @return array
	 */
	protected function getOtherDaysNumberOfSlots($widget_id)
	{
		$w_form = WidgetForm::where('restaurant_id', $this->id)->first();
		if ($w_form) {
			$number_days = $w_form->form_config['number_of_other_days_to_display'];
			$number_of_slots = $w_form->form_config['number_slots_display_for_other_days'];
		} else {
			$number_days = 4;
			$number_of_slots = 3;
		}

		return ['number_days' => $number_days, 'number_of_slots' => $number_of_slots];
	}

}
