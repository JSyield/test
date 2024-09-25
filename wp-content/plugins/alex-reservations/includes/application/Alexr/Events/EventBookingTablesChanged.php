<?php

namespace Alexr\Events;

use Alexr\Models\Booking;
use Alexr\Models\User;

class EventBookingTablesChanged
{
	public $booking;
	public $old_tables;
	public $new_tables;
	public $user;

	public function __construct(Booking $booking, $old_tables, $new_tables, User $user = null)
	{
		//ray('Event Booking status changed');
		$this->booking = $booking;
		$this->old_tables = $old_tables;
		$this->new_tables = $new_tables;
		$this->user = $user;
	}
}
