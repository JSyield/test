<?php

namespace Evavel\Providers;

//use Alexr\Enums\BookingStatus;
//use Alexr\Events\EventBookingStatusChanged;
//use Alexr\Listeners\BookingStatusListener;
//use Alexr\Models\Booking;

use Evavel\Container\EvaContainer;
use Evavel\Eva;
use Evavel\Events\Dispatcher;
use Evavel\Events\ExampleListener;
use Evavel\Models\Model;

class EventServiceProvider extends ServiceProvider
{
	protected $listen = [];

	protected $observers = [];


	public function register()
	{
		// Register dispatcher singleton
		Eva::bind('events', new Dispatcher());
	}

	public function boot()
	{
		// Attach dispatcher to model so observers can be loaded
		Model::setEventDispatcher(evavel_make('events'));

		$this->loadObservers();
		$this->loadListeners();


		// Example
		//$this->exampleNormalEvents();
		//$this->exampleListenerEvents();
	}


	public function loadObservers() {}

	public function loadListeners()
	{
		foreach($this->listen as $event => $listeners_class){
			foreach($listeners_class as $listener_class)
				evavel_listen($event, $listener_class);
		}
	}





	/*
	public function exampleListenerEvents()
	{
		evavel_listen(EventBookingStatusChanged::class, BookingStatusListener::class);
		evavel_event(new EventBookingStatusChanged(Booking::first(),BookingStatus::PENDING, BookingStatus::CONFIRMED));
	}

	public function exampleNormalEvents()
	{
		// Example how to use it
		evavel_make('events')->listen('kkk', function($name1, $name2) {
			return 'closure: '.$name1 . ','. $name2;
		});
		evavel_make('events')->listen('kkk', [ExampleListener::class, 'handle']);
		evavel_make('events')->listen('kkk', ExampleListener::class.'@handle');
		evavel_listen('kkk', ExampleListener::class.'@handle');

		$result = evavel_make('events')->dispatch('kkk', ['ALEJANDRO', 'BRUNO']);
		ray($result);

		$result = evavel_event('kkk', ['EVA', 'LEON']);
		ray($result);


		// Call array as function
		//ray([new ExampleListener(), 'handle']('PEPE','JOSE'));
	}*/
}

