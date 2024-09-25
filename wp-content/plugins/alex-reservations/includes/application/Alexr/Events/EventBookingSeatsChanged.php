<?php

namespace Alexr\Events;

use Alexr\Models\Booking;
use Alexr\Models\User;

class EventBookingSeatsChanged
{
	public $booking;
	public $old_seats;
	public $new_seats;
	public $user;

	public function __construct(Booking $booking, $old_seats, $new_seats, User $user = null)
	{
		//ray('Event Booking status changed');
		$this->booking = $booking;
		$this->old_seats = $old_seats;
		$this->new_seats = $new_seats;
		$this->user = $user;
	}
}
