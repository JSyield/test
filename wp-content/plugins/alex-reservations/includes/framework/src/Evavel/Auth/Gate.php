<?php

namespace Evavel\Auth;


use Evavel\Eva;
use Evavel\Models\Collections\Arr;
use Evavel\Models\Model;
use Evavel\Support\Str;

class Gate
{
	protected $user = null;

	/**
	 * Defined abilities.
	 *
	 * @var array
	 */
	protected $abilities = [];

	/**
	 * Define all abilities with class@method
	 *
	 * @var array
	 */
	protected $stringCallbacks = [];

	/**
	 * Define policies
	 *
	 * @var array
	 */
	protected $policies = [];

	/**
	 * Set the user to be used for the callback
	 *
	 * @param $user
	 *
	 * @return $this
	 */
	public function forUser($user)
	{
		$this->user = $user;

		return $this;
	}

	/**
	 * Define ability
	 *
	 * @param $ability
	 * @param $callback
	 *
	 * @return $this
	 * @throws \Exception
	 */
	public function define($ability, $callback)
	{
		// Call back is array [class, method]
		if (is_array($callback) && isset($callback[0]) && is_string($callback[0])) {
			$callback = $callback[0].'@'.$callback[1];
		}


		// Closure
		if (is_callable($callback)) {
			$this->abilities[$ability] = $callback;
		}
		// Class@method
		elseif (is_string($callback)) {
			$this->stringCallbacks[$ability] = $callback;
			$this->abilities[$ability] = $this->buildAbilityCallback($ability, $callback);
		}
		else {
			throw new \Exception("Callback has to be a closure or a class@method string");
		}

		return $this;
	}

	/**
	 * Create a policy entry
	 *
	 * @param Model $class
	 * @param $policy
	 *
	 * @return $this
	 */
	public function policy($class, $policy)
	{
		$this->policies[$class] = $policy;

		return $this;
	}

	/**
	 * Create callable for class method
	 *
	 * @param $ability
	 * @param $callback
	 *
	 * @return \Closure
	 */
	protected function buildAbilityCallback($ability, $callback)
	{
		return function () use ($ability, $callback) {

			if (Str::contains($callback, '@')) {
				$class_method_arr = Str::parseCallback($callback);
				$class = $class_method_arr[0];
				$method = $class_method_arr[1];
			} else {
				$class = $callback;
			}

			$policy = $this->resolvePolicy($class);

			/* Pending.. call policy before

			$arguments = func_get_args();

			$user = array_shift($arguments);

			$result = $this->callPolicyBefore(
				$policy, $user, $ability, $arguments
			);

			if (! is_null($result)) {
				return $result;
			}
			*/

			return isset($method)
				? $policy->{$method}(...func_get_args())
				: $policy(func_get_args());

		};
	}

	/**
	 * Resolve using the container or create class directly
	 *
	 * @param $class
	 *
	 * @return mixed
	 */
	public function resolvePolicy($class)
	{
		return evavel_make($class);
	}

	/**
	 * Determine ability/s has been defined.
	 *
	 * @param  string|array  $ability
	 * @return bool
	 */
	public function has($ability)
	{
		$abilities = is_array($ability) ? $ability : func_get_args();

		foreach ($abilities as $ability) {
			if (! isset($this->abilities[$ability])) {
				return false;
			}
		}

		return true;
	}

	/**
	 * Check
	 *
	 * @param string|iterable $abilities
	 * @param array $arguments
	 *
	 * @return bool
	 */
	public function check($abilities, $arguments = [])
	{
		foreach ((array) $abilities as $ability){
			$result = $this->inspect($ability, $arguments);
			if (!$result) return false;
		}

		return true;
	}

	/**
	 * Call the callback
	 *
	 * @param $ability
	 * @param $arguments
	 *
	 * @return bool
	 */
	public function inspect($ability, $arguments)
	{
		//ray($ability);
		//ray($arguments);

		$user = $this->resolveUser();

		$arguments = Arr::wrap($arguments);

		return $this->callAuthCallback($user, $ability, $arguments);
	}

	/**
	 * Get the user from the container
	 * if has not been set yet using forUser method
	 *
	 * @return mixed|null
	 */
	public function resolveUser()
	{
		$user = $this->user !== null ? $this->user : Eva::make('user');
		$this->user = null;

		return $user;
	}

	/**
	 * Call the closure to resolve the permission
	 *
	 * @param $user
	 * @param $ability
	 * @param array $arguments
	 *
	 * @return bool
	 */
	protected function callAuthCallback($user, $ability, array $arguments)
	{
		$callback = $this->resolveAuthCallback($user, $ability, $arguments);

		return $callback($user, ...$arguments);
	}

	/**
	 * Resolve the callback to use
	 * Try Policy first, then the ability
	 *
	 * @param $user
	 * @param $ability
	 * @param array $arguments
	 *
	 * @return \Closure
	 */
	protected function resolveAuthCallback($user, $ability, array $arguments)
	{
		/*if ($ability == 'view' && isset($arguments[0])) {
			//ray($this->policies);
			//ray($arguments[0]);
			//ray($this->getPolicyFor($arguments[0]));
		}*/


		if (isset($arguments[0]) &&
		    ! is_null($policy = $this->getPolicyFor($arguments[0])) &&
		    $callback = $this->resolvePolicyCallback($user, $ability, $arguments, $policy)) {
			return $callback;
		}

		if (isset($this->abilities[$ability])) {
		    return $this->abilities[$ability];
		}

		// By default is allowed
		return function () {
			return true;
		};
	}

	/**
	 * Resolve the Policy class
	 *
	 * @param $class
	 *
	 * @return mixed|void
	 */
	protected function getPolicyFor($class)
	{
		if (is_object($class)){
			$class = get_class($class);
		}

		if (! is_string($class)) {
			return;
		}


		if (isset($this->policies[$class])) {
			//ray('FOUND POLICY FOR CLASS: '.$class);
			//ray($this->resolvePolicy($this->policies[$class]));
			return $this->resolvePolicy($this->policies[$class]);
		}
	}

	/**
	 * Call the Policy method
	 *
	 * @param $user
	 * @param $ability
	 * @param $arguments
	 * @param $policy
	 *
	 * @return \Closure
	 */
	protected function resolvePolicyCallback($user, $ability, $arguments, $policy)
	{
		return function() use($user, $ability, $arguments, $policy) {

			if (method_exists($policy, $ability)){
				return $policy->{$ability}($user, ...$arguments);
			}

			return true;
		};
	}

	/**
	 * Check if ability is allowed
	 *
	 * @param $ability
	 * @param $arguments
	 *
	 * @return bool
	 */
	public function allows($ability, $arguments = [])
	{
		return $this->check($ability, $arguments);
	}

	/**
	 * Check if ability is denied
	 *
	 * @param $ability
	 * @param $arguments
	 *
	 * @return bool
	 */
	public function denies($ability, $arguments = [])
	{
		return ! $this->allows($ability, $arguments);
	}
}
