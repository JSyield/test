<?php

namespace Alexr\Models\Traits;

trait ManageAddToCalendar {

	public function getCalendarLinks(){

		if (function_exists('alexr_add_to_calendar_links')){
			return alexr_add_to_calendar_links($this);
		}

		return [
			'apple' => null,
			'google' => null,
			'outlook' => null
		];
	}
}
