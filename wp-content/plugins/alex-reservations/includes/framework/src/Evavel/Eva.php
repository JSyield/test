<?php

namespace Evavel;

/**
 * @method static \Evavel\Container\EvaContainer bind(string $name,mixed $arguments)
 * @method static mixed make(string $name)
 * @method static null registerProviders(array $providers)
 * @method static null bootProviders(array $providers)
 * @method static null addConfig(array $values)
 */
class Eva
{
	public static function __callStatic($name, $arguments)
	{
		return \Evavel\Container\EvaContainer::singleton()->$name(...$arguments);
	}
}
