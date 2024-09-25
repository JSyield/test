<?php

function alexr_get_service($service_id)
{
	$service = \Alexr\Settings\Shift::where('id', $service_id)->first();
	if (!$service) {
		$service = \Alexr\Settings\Event::where('id', $service_id)->first();
	}
	return $service;
}

function alexr_is_range_overlapping( $startTime_1, $endTime_1, $startTime_2, $endTime_2) {

	// Is same interval or start and ends at the same time
	if ($startTime_1 == $startTime_2 || $endTime_1 == $endTime_2) return true;

	// Is included 1 into 2 completely
	if ($startTime_1 > $startTime_2 && $endTime_1 < $endTime_2) return true;

	// Is included 2 into 1 completely
	if ($startTime_2 > $startTime_1 && $endTime_2 < $endTime_1) return true;

	// start 1 is included in 2
	if ($startTime_1 > $startTime_2 && $startTime_1 < $endTime_2) return true;

	// end 1 is included in 2
	if ($endTime_1 > $startTime_2 && $endTime_1 < $endTime_2) return true;

	return false;
}
