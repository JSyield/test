<?php

namespace Alexr\Models\Traits;

trait CalculateBookingMetrics {

	// Calculate occupancy fo intervals as a bar chart every 15 minutes
	// Ordered in columns by booking guests
	/*
	          3 3
	    3 3   2 4 2
		4 4 3 6 2 2 4 4 4
		- - - - - - - - -
	*/

	public function getOccupanyForDate($date) {

		// Get bookings that will be used for calculations

		return [
			20*3600 => [4,2,2,4,6],
			20*3600+900 => [4,2,2,4,6,2],
			20*3600+1800 => [4,2,2,4],
			20*3600+2700 => [4,2,2,4],
			21*3600 => [4,2,2,2,2],
			21*3600+900 => [4,2,2,2,2],
			21*3600+900 => [4,2,2],
			21*3600+900 => [4,2,2],
			22*3600 => [4,2,2,2,2],
			22*3600+900 => [4,2,2,2,2],
			22*3600+900 => [4,2,2],
			22*3600+900 => [4,2,2],
			23*3600 => [4,2,2,2,2],
			23*3600+900 => [4,2,2,2,2],
			23*3600+900 => [4,2,2],
			23*3600+900 => [4,2,2],
		];

	}
}
