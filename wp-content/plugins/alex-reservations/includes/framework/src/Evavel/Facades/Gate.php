<?php

namespace Evavel\Facades;

use Evavel\Models\Model;
use Evavel\Models\User;

/**
 * @method static \Evavel\Auth\Gate define(string $ability, callable|string $callback)
 *
 * @method static bool has(string $ability)
 * @method static bool check(iterable|string $abilities, array|mixed $arguments = [])
 * @method static bool allows(string $ability, array|mixed $arguments = [])
 * @method static bool denies(string $ability, array|mixed $arguments = [])
 * @method static \Evavel\Auth\Gate policy(Model $class, \stdClass $policy)
 *
 * @method static \Evavel\Auth\Gate forUser(Evavel\Models\User $user)
 * @see \Evavel\Auth\Gate
 */
class Gate extends Facade
{
	protected static function getFacadeAccessor()
	{
		return 'gate';
	}
}
