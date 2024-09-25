<?php

namespace Evavel\Events;

use Evavel\Container\EvaContainer;
use Evavel\Events\Traits\Dispatcher as DispatcherContract;
use Evavel\Models\Collections\Arr;

class Dispatcher implements DispatcherContract
{
	protected $listeners = [];
	protected $container;

	public function __construct($container = null)
	{
		$this->container = $container ?: EvaContainer::singleton();
	}


	public function listen( $events, $listener = null )
	{
		foreach((array) $events as $event) {
			$this->listeners[$event][] = $this->makeListener($listener);
		}
	}

	public function makeListener($listener)
	{
		if (is_string($listener)){
			return $this->createClassListener($listener);
		}

		if (is_array($listener) && isset($listener[0]) && is_string($listener[0])) {
			return $this->createClassListener($listener);
		}

		return function ($event, $payload) use ($listener) {

			return $listener(...array_values($payload));
		};
	}

	public function createClassListener($listener)
	{
		return function($event, $payload) use ($listener) {

			$callable = $this->createClassCallable($listener);

			// $callable is an array[className, method] than can be called
			return $callable(...array_values($payload));
		};
	}

	protected function createClassCallable($listener)
	{
		// $listener = [className, method]

		if (is_array($listener)){
			$class = $listener[0];
			$method = $listener[1];
		} else {
			if (!preg_match('#@#', $listener)){
				$listener .= '@handle';
			}
			$data = explode('@', $listener);
			$class = $data[0];
			$method = $data[1];
		}

		$listener = $this->container->resolve($class);

		return [$listener, $method];
	}

	public function dispatch($event, $payload = [])
	{
		$responses = [];
		$payload = Arr::wrap($payload);

		// If event is an object will use the event class as the event
		// and the event object will be the payload
		if (is_object($event)){
			$payload = [$event];
			$event = get_class($event);
		}
		$payload = Arr::wrap($payload);

		foreach ($this->getListeners($event) as $listener) {
			$response = $listener($event, $payload);
			if ($response === false) {
				break;
			}
			$responses[] = $response;
		}

		return $responses;
	}

	public function getListeners($eventName)
	{
		//ray($eventName);
		return isset($this->listeners[$eventName]) ?  $this->listeners[$eventName] : [];
	}

	public function hasListeners( $eventName ) {
		// TODO: Implement hasListeners() method.
	}
}
