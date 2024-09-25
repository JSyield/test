<?php

namespace Evavel\Events\Traits;

interface Dispatcher
{
	/**
	 * Register an event listener with the dispatcher.
	 *
	 * @param  string|array  $events
	 * @param  \Closure|string|array|null  $listener
	 * @return void
	 */
	public function listen($events, $listener = null);

	/**
	 * Determine if a given event has listeners.
	 *
	 * @param  string  $eventName
	 * @return bool
	 */
	public function hasListeners($eventName);

	/**
	 * Dispatch an event and call the listeners.
	 *
	 * @param  string|object  $event
	 * @param  mixed  $payload
	 * @return array|null
	 */
	public function dispatch($event, $payload = []);

}
