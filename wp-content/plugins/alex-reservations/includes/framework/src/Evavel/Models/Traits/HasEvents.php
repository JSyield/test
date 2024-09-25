<?php

namespace Evavel\Models\Traits;

use Evavel\Events\Traits\Dispatcher;
use Evavel\Models\Collections\Arr;

trait HasEvents
{
	protected static $dispatcher;

	public static function getEventDispatcher()
	{
		return static::$dispatcher;
	}

	public static function setEventDispatcher(Dispatcher $dispatcher)
	{
		static::$dispatcher = $dispatcher;
	}

	public static function unsetEventDispatcher()
	{
		static::$dispatcher = null;
	}

	public static function observe($classes)
	{
		$instance = new static;

		foreach ( Arr::wrap($classes) as $class) {
			$instance->registerObserver($class);
		}
	}

	protected function registerObserver($class)
	{
		//ray('registerObserver: '.$class);
		//ray(static::class);

		$className = $this->resolveObserverClassName($class);

		foreach ($this->getObservableEvents() as $event) {
			if (method_exists($class, $event)) {
				static::registerModelEvent($event, $className.'@'.$event);
			}
		}
	}

	public function getObservableEvents()
	{
		return [
			'retrieved',
			'creating', 'created',
			'updating', 'updated',
			'saving', 'saved',
			'deleting', 'deleted'
		];
	}

	private function resolveObserverClassName($class)
	{
		if (is_object($class)) {
			return get_class($class);
		}

		if (class_exists($class)) {
			return $class;
		}

		throw new \Error('Invalid observer class: '.$class);
	}

	protected static function registerModelEvent($event, $callback)
	{
		if (isset(static::$dispatcher)) {
			$name = static::class;
			//ray('listen: '."database.{$event}: {$name}");
			static::$dispatcher->listen("database.{$event}: {$name}", $callback);
		}
	}

	protected function fireModelEvent($event)
	{
		if (! isset(static::$dispatcher)) {
			return true;
		}

		return static::$dispatcher->dispatch("database.{$event}: ".static::class, $this);
	}

	public static function saving($callback)
	{
		static::registerModelEvent('saving', $callback);
	}

	public static function saved($callback)
	{
		static::registerModelEvent('saved', $callback);
	}

	public static function updating($callback)
	{
		static::registerModelEvent('updating', $callback);
	}

	public static function updated($callback)
	{
		static::registerModelEvent('updated', $callback);
	}

	public static function creating($callback)
	{
		static::registerModelEvent('creating', $callback);
	}

	public static function created($callback)
	{
		static::registerModelEvent('created', $callback);
	}
}
