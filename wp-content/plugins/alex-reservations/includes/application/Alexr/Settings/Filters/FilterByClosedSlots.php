<?php

namespace Alexr\Settings\Filters;

use Alexr\Settings\ClosedSlot;
use Alexr\Settings\Interfaces\FilterSlots;
use Alexr\Settings\Scheduler;

// Slots cerrados
class FilterByClosedSlots implements FilterSlots {

	public function apply( Scheduler $shift, $arr_slots, $guests, $date, $time, $numberOfSlotsToGet = null ) {

		$closedSlots = ClosedSlot::where(evavel_tenant_field(), $shift->{evavel_tenant_field()})->first();

		if ($closedSlots)
		{
			$data = $closedSlots->{$date};

			if ($data){

				foreach($data as $item){
					if ($item['id'] == $shift->id) {
						$slots_closed = $item['slots'];

						if (is_array($slots_closed)){
							$new_arr_slots = [];
							foreach($arr_slots as $arr_slot){
								if (!in_array($arr_slot, $slots_closed)){
									$new_arr_slots[] = $arr_slot;
								}
							}
							$arr_slots = $new_arr_slots;
						}

					}
				}
			}

		}

		return $arr_slots;
	}
}
