<?php

namespace Alexr\Settings\Filters;

use Alexr\Models\Restaurant;
use Alexr\Settings\General;
use Alexr\Settings\Interfaces\FilterSlots;
use Alexr\Settings\Scheduler;
use Alexr\Settings\WidgetForm;

class FilterByDuration implements FilterSlots {

	public function apply( Scheduler $shift, $arr_slots, $guests, $date, $time, $numberOfSlotsToGet = null ) {

		// Not using booking_duration from the widget now, it is defined in the shift only
		/*$w_form = WidgetForm::where('restaurant_id', $shift->restaurant_id)->first();
		if ($w_form) {
			$duration_time = $w_form->form_config['booking_duration'];
		} else {
			$duration_time = 3600;
		}*/
		$duration_time = 3600;


		$duration_mode = $shift->duration_mode;
		if ($duration_mode == 'time') {
			$duration_time = $shift->duration_time;
		}
		else if ($duration_mode == 'covers') {
			$duration_covers = $shift->duration_covers;
			foreach ($duration_covers as $item){
				if ($guests >= $item['label']){
					$duration_time = $item['value'];
				}
			}
		}

		//$last_possible_slot = $shift->last_seating - $duration_time;
		$last_possible_slot = $shift->getLastSeatingForDate($date) - $duration_time;

		$final_slots = array_filter($arr_slots, function($slot) use($last_possible_slot){
			return $slot <= $last_possible_slot;
		});

		return $final_slots;
	}
}
