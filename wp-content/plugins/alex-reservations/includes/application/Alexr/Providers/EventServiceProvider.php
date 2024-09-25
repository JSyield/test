<?php

namespace Alexr\Providers;

use Alexr\Enums\BookingStatus;
use Alexr\Events\EventBookingSeatsChanged;
use Alexr\Events\EventBookingStatusChangedByCustomer;
use Alexr\Events\EventBookingStatusChanged;
use Alexr\Events\EventBookingCreated;
use Alexr\Events\EventBookingModified;
use Alexr\Events\EventBookingTablesChanged;
use Alexr\Listeners\BookingActionLogListener;
use Alexr\Listeners\BookingStatusListener;
use Alexr\Listeners\ListenBookingEvents;
use Alexr\Models\Booking;
use Alexr\Models\Table;
use Alexr\Models\Token;
use Alexr\Observers\BookingObserver;
use Alexr\Observers\TableObserver;
use Alexr\Observers\TokenObserver;
use Evavel\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider {

	protected $listen = [
		//EventBookingStatusChanged::class => [BookingStatusListener::class,BookingActionLogListener::class],
		EventBookingStatusChanged::class => [ListenBookingEvents::class],
		EventBookingCreated::class => [ListenBookingEvents::class],
		EventBookingModified::class => [ListenBookingEvents::class],
		EventBookingTablesChanged::class => [ListenBookingEvents::class],
		EventBookingSeatsChanged::class => [ListenBookingEvents::class],
		EventBookingStatusChangedByCustomer::class => [ListenBookingEvents::class],
	];

    public function register()
    {
	    parent::register();
    }

	public function boot()
	{
		parent::boot(); // This calls loadObservers()

		// Example trigger an event
		//evavel_listen(EventBookingStatusChanged::class, BookingStatusListener::class);
		//evavel_event(new EventBookingStatusChanged(Booking::first(),BookingStatus::PENDING, BookingStatus::CONFIRMED));
	}

	public function loadObservers()
	{
		Booking::observe(BookingObserver::class);
		Token::observe(TokenObserver::class);
		Table::observe(TableObserver::class);
	}

}
