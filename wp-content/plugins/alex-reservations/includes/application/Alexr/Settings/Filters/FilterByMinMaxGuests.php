<?php

namespace Alexr\Settings\Filters;

use Alexr\Models\Restaurant;
use Alexr\Settings\General;
use Alexr\Settings\Interfaces\FilterSlots;
use Alexr\Settings\Scheduler;
use Alexr\Settings\WidgetForm;

class FilterByMinMaxGuests implements FilterSlots {

	public function apply( Scheduler $shift, $arr_slots, $guests, $date, $time, $numberOfSlotsToGet = null )
	{
		$min = intval($shift->min_covers_reservation);
		$max = intval($shift->max_covers_reservation);
		if ($guests < $min || $guests > $max) {
			return [];
		}
		return $arr_slots;
	}
}
