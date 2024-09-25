<?php

namespace Evavel\Providers;

use Evavel\Auth\Gate;
use Evavel\Container\EvaContainer;
use Evavel\Eva;

class AuthServiceProvider extends ServiceProvider
{
	protected $policies = [];

	public function register()
	{
		Eva::bind('gate', new Gate());
	}

	public function boot()
	{
		$this->registerPolicies();
	}

	public function registerPolicies()
	{
		foreach ($this->policies() as $key => $value){
			\Evavel\Facades\Gate::policy($key, $value);
		}
	}

	/**
	 * Get the policies defined on the provider.
	 *
	 * @return array
	 */
	public function policies()
	{
		return $this->policies;
	}
}
