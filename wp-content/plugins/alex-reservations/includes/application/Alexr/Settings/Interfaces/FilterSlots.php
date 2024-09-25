<?php

namespace Alexr\Settings\Interfaces;

use Alexr\Settings\Scheduler;

interface FilterSlots {
	public function apply(Scheduler $shift, $arr_slots, $guests, $date, $time, $numberOfSlotsToGet = null);
}
