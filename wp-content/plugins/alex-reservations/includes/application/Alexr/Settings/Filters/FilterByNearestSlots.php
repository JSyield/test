<?php

namespace Alexr\Settings\Filters;

use Alexr\Settings\General;
use Alexr\Settings\Interfaces\FilterSlots;
use Alexr\Settings\Scheduler;
use Alexr\Settings\Shift;
use Alexr\Settings\WidgetForm;

class FilterByNearestSlots implements FilterSlots {

	public function apply( Scheduler $shift, $arr_slots, $guests, $date, $time, $numberOfSlotsToGet = null ) {

		if (!$numberOfSlotsToGet){
			$w_form = WidgetForm::where('restaurant_id', $shift->restaurant_id)->first();
			if ($w_form) {
				$number_slots = $w_form->form_config['number_slots_display'];
			} else {
				$number_slots = 4;
			}
		} else {
			$number_slots = $numberOfSlotsToGet;
		}


		// Select X slots with the minimum distance
		$final_slots = [];
		for ($i = 1; $i <= $number_slots; $i++) {

			$slot_selected = false;
			$slot_selected_distance = 100000;

			// Find the slot with min distance not included in final_slots
			foreach($arr_slots as $slot) {
				if (!in_array($slot, $final_slots)) {
					$slot_distance = abs($slot - $time);
					if ($slot_distance < $slot_selected_distance) {
						$slot_selected = $slot;
						$slot_selected_distance = $slot_distance;
					}
				}
			}

			if ($slot_selected) {
				$final_slots[] = $slot_selected;
			}
		}

		asort($final_slots);
		return $final_slots;
	}
}
