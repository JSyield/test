<?php

namespace Evavel\Models\Traits;

use Evavel\Facades\Gate;

trait Authorizable
{
	/**
	 * Determine if the entity has the given abilities.
	 *
	 * @param  iterable|string  $abilities
	 * @param  array|mixed  $arguments
	 * @return bool
	 */
	public function can($abilities, $arguments = [])
	{
		return Gate::forUser($this)->allows($abilities, $arguments);
	}

	/**
	 * Determine if the entity has not the given abilities.
	 *
	 * @param  iterable|string  $abilities
	 * @param  array|mixed  $arguments
	 * @return bool
	 */
	public function cannot($abilities, $arguments = [])
	{
		return !$this->can($abilities, $arguments);
	}
}
