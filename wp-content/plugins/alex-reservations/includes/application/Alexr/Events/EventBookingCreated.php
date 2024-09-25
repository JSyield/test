<?php

namespace Alexr\Events;

use Alexr\Models\Booking;
use Alexr\Models\Customer;
use Alexr\Models\User;

class EventBookingCreated
{
	public $booking;
	public $user;

	public function __construct(Booking $booking, User $user = null)
	{
		$this->booking = $booking;
		$this->user = $user;
	}
}
