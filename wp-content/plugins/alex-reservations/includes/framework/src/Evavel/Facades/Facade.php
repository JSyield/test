<?php

namespace Evavel\Facades;

use http\Exception\RuntimeException;

abstract class Facade
{
	public static function __callStatic($method, $args)
	{
		$instance = static::getFacadeRoot();

		if (!$instance) {
			throw new RuntimeException('Facade could not be created.');
		}

		return $instance->$method(...$args);
	}

	public static function getFacadeRoot()
	{
		return static::resolveFacadeInstance(static::getFacadeAccessor());
	}

	protected static function resolveFacadeInstance($name)
	{
		if (is_object($name)) {
			return $name;
		}

		return evavel_make($name);
	}

	protected static function getFacadeAccessor()
	{
		throw new RuntimeException('Facade does not implement getFacadeAccessor method.');
	}
}
