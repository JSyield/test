<?php

namespace Alexr\Settings\Filters;

use Alexr\Settings\Interfaces\FilterSlots;
use Alexr\Settings\Scheduler;

class FilterByBlockSlots implements FilterSlots {

	public function apply( Scheduler $shift, $arr_slots, $guests, $date, $time, $numberOfSlotsToGet = null ) {
		$slots_to_remove = [];

		$block_slots = $shift->block_slots;

		foreach($block_slots as $item) {
			if ($item['block'] === true) {
				$slots_to_remove[] = $item['time'];
			}
		}

		$list = array_diff($arr_slots, $slots_to_remove);

		return array_values(array_diff($arr_slots, $slots_to_remove));
	}
}
