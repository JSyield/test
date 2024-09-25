<?php

namespace Alexr\Providers;

use Alexr\Enums\UserRole;
use Alexr\Models\Booking;
use Alexr\Models\Restaurant;
use Alexr\Models\User;
use Alexr\Policies\BookingPolicy;
use Alexr\Policies\RestaurantPolicy;
use Alexr\Policies\UserPolicy;
use Evavel\Container\EvaContainer;
use Evavel\Eva;
use Evavel\Models\Model;
use Evavel\Providers\AuthServiceProvider as ServiceProvider;
use Evavel\Facades\Gate;

class AuthServiceProvider extends ServiceProvider
{
	protected $policies = [
		Restaurant::class => RestaurantPolicy::class,
		Booking::class => BookingPolicy::class,
		User::class => UserPolicy::class
	];

	public function register()
	{
		parent::register();
	}

	public function boot()
	{
		parent::boot();

		$this->registerGatesForModels();
		$this->registerGatesForSettings();
	}

	/**
	 * Register settings for tenant y main
	 *
	 * @return void
	 */
	protected function registerGatesForSettings()
	{
		Gate::define('settings-tenant', function($user, $tenantId) {

			switch ($user->role) {
				case UserRole::ADMINISTRATOR:
					return true;
				//case UserRole::OWNER:
				//	return in_array($tenantId, $user->restaurantsIds());
				//case UserRole::EMPLOYE:
				//	return $user->restaurant->id == $tenantId;
			}

			return false;
		});

		Gate::define('settings-main', function($user) {
			return $user->role == UserRole::ADMINISTRATOR;
		});
	}

	/**
	 * Global gates for all models
	 * Use Policy for specific model class
	 *
	 * @return void
	 */
	protected function registerGatesForModels()
	{
		Gate::define('view-dashboard', function($user) {
			return in_array($user->role, [UserRole::ADMINISTRATOR, UserRole::USER]);
			//return in_array($user->role, [UserRole::ADMINISTRATOR, UserRole::SUPER_MANAGER, UserRole::MANAGER, UserRole::SUB_MANAGER]);
		});

		Gate::define('viewAny', function($user, $modelClass){
			return true;
		});

		Gate::define('create', function($user, $modelClass){
			return true;
		});

		Gate::define('view', function($user, Model $model){
			return true;
		});

		Gate::define('update', function($user, Model $model){
			return true;
		});

		Gate::define('delete', function($user, Model $model){
			return true;
		});
	}

}
