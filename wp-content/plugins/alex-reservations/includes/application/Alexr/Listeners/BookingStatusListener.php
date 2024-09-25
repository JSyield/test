<?php

namespace Alexr\Listeners;

class BookingStatusListener
{
	public function __construct()
	{
		//ray("BookingStatusListener construct");
	}

	public function handle($event)
	{
		//ray('BookingStatusListener event received');
		//ray($event);
		//ray($event->booking);
	}
}
