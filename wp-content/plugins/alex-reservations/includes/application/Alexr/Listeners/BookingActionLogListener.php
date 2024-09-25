<?php

namespace Alexr\Listeners;

class BookingActionLogListener
{
	public function __construct()
	{
		//ray("BookingActionLogListener construct");
	}

	public function handle($event)
	{
		//ray('BookingActionLogListener event received');
		//ray($event);
		//ray($event->booking);
	}
}
