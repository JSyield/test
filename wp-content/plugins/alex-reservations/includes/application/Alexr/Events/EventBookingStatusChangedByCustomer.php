<?php

namespace Alexr\Events;

use Alexr\Models\Booking;
use Alexr\Models\User;

class EventBookingStatusChangedByCustomer
{
	public $booking;
	public $old_status;
	public $new_status;

	public function __construct(Booking $booking, $old_status, $new_status)
	{
		$this->booking = $booking;
		$this->old_status = $old_status;
		$this->new_status = $new_status;
	}
}
