<?php

namespace Alexr\Events;

use Alexr\Models\Booking;
use Alexr\Models\User;

class EventBookingModified
{
	public $booking;
	public $user;
	public $old_attributes;

	public function __construct(Booking $booking, User $user = null, $old_attributes = [])
	{
		$this->booking = $booking;
		$this->user = $user;
		$this->old_attributes = $old_attributes;
	}
}
