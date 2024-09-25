<?php

namespace Alexr\Observers;

use Alexr\Models\Booking;
use Evavel\Eva;
use Evavel\Support\Str;

class BookingObserver
{
	public function saving(Booking $booking)
	{
		//ray('Booking is saving '.$booking->id);
	}

	public function saved(Booking $booking)
	{
		//ray('Booking has been saved '.$booking->id);
	}

	public function updating(Booking $booking)
	{
		//ray('Booking is updating '.$booking->id);
	}

	public function updated(Booking $booking)
	{
		//ray('Booking has been updated '.$booking->id);
	}

	public function creating(Booking $booking)
	{
		//ray('Booking is creating '.$booking->id);
	}

	public function created(Booking $booking)
	{
		//ray('Booking has been created '.$booking->id);
	}
}
